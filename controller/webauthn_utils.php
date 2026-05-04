<?php

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function cbor_decode(string $input, int &$offset = 0) {
    $initialByte = ord($input[$offset++]);
    $majorType = $initialByte >> 5;
    $additionalInfo = $initialByte & 0x1f;

    $length = 0;
    if ($additionalInfo < 24) {
        $length = $additionalInfo;
    } elseif ($additionalInfo === 24) {
        $length = ord($input[$offset++]);
    } elseif ($additionalInfo === 25) {
        $length = unpack('n', substr($input, $offset, 2))[1];
        $offset += 2;
    } elseif ($additionalInfo === 26) {
        $length = unpack('N', substr($input, $offset, 4))[1];
        $offset += 4;
    } elseif ($additionalInfo === 27) {
        $hi = unpack('N', substr($input, $offset, 4))[1];
        $lo = unpack('N', substr($input, $offset + 4, 4))[1];
        $offset += 8;
        $length = $hi << 32 | $lo;
    } elseif ($additionalInfo === 31) {
        $length = null;
    }

    switch ($majorType) {
        case 0:
            return $length;
        case 1:
            return -1 - $length;
        case 2:
            $value = substr($input, $offset, $length);
            $offset += $length;
            return $value;
        case 3:
            $value = substr($input, $offset, $length);
            $offset += $length;
            return $value;
        case 4:
            $array = [];
            for ($i = 0; $length === null ? true : $i < $length; $i++) {
                if ($length === null && ord($input[$offset]) === 0xff) {
                    $offset++;
                    break;
                }
                $array[] = cbor_decode($input, $offset);
            }
            return $array;
        case 5:
            $map = [];
            for ($i = 0; $length === null ? true : $i < $length; $i++) {
                if ($length === null && ord($input[$offset]) === 0xff) {
                    $offset++;
                    break;
                }
                $key = cbor_decode($input, $offset);
                $value = cbor_decode($input, $offset);
                $map[$key] = $value;
            }
            return $map;
        case 6:
            return cbor_decode($input, $offset);
        case 7:
            if ($additionalInfo === 20) return false;
            if ($additionalInfo === 21) return true;
            if ($additionalInfo === 22) return null;
            if ($additionalInfo === 23) return null;
            if ($additionalInfo === 24) {
                $simple = ord($input[$offset++]);
                return $simple;
            }
            if ($additionalInfo === 25) {
                $data = substr($input, $offset, 2);
                $offset += 2;
                $value = unpack('n', $data)[1];
                return $value;
            }
            if ($additionalInfo === 26) {
                $data = substr($input, $offset, 4);
                $offset += 4;
                $value = unpack('N', $data)[1];
                return $value;
            }
            if ($additionalInfo === 27) {
                $hi = unpack('N', substr($input, $offset, 4))[1];
                $lo = unpack('N', substr($input, $offset + 4, 4))[1];
                $offset += 8;
                return ($hi << 32) | $lo;
            }
            return null;
    }
    return null;
}

function parse_authenticator_data(string $authData): array {
    $rpIdHash = substr($authData, 0, 32);
    $flags = ord($authData[32]);
    $signCount = unpack('N', substr($authData, 33, 4))[1];
    $offset = 37;

    $result = [
        'rpIdHash' => $rpIdHash,
        'flags' => $flags,
        'signCount' => $signCount,
        'attestedCredentialData' => null,
    ];

    if ($flags & 0x40) {
        $aaguid = substr($authData, $offset, 16);
        $offset += 16;
        $credIdLen = unpack('n', substr($authData, $offset, 2))[1];
        $offset += 2;
        $credentialId = substr($authData, $offset, $credIdLen);
        $offset += $credIdLen;
        $publicKey = cbor_decode($authData, $offset);

        $result['attestedCredentialData'] = [
            'aaguid' => $aaguid,
            'credentialId' => $credentialId,
            'publicKey' => $publicKey,
        ];
    }

    return $result;
}

function cose_key_to_pem(array $cose): ?string {
    if (!isset($cose[1]) || !isset($cose[3])) {
        return null;
    }
    $kty = $cose[1];
    if ($kty === 2 && isset($cose[-1], $cose[-2], $cose[-3])) {
        $crv = $cose[-1];
        $x = $cose[-2];
        $y = $cose[-3];
        if ($crv !== 1) {
            return null;
        }
        $pubKey = "\x04" . $x . $y;
        $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $pubKey;
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
        return $pem;
    }
    if ($kty === 3 && isset($cose[-1], $cose[-2])) {
        $n = $cose[-1];
        $e = $cose[-2];
        $der = rsa_public_key_der($n, $e);
        if ($der === null) {
            return null;
        }
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
        return $pem;
    }
    return null;
}

function rsa_public_key_der(string $n, string $e): ?string {
    $modulus = asn1_encode_integer($n);
    $exponent = asn1_encode_integer($e);
    $sequence = asn1_encode_sequence($modulus . $exponent);
    $bitString = asn1_encode_bitstring("\x00" . $sequence);
    $header = hex2bin('305d300d06092a864886f70d0101010500');
    return $header . $bitString;
}

function asn1_encode_length(int $length): string {
    if ($length < 128) {
        return chr($length);
    }
    $hex = dechex($length);
    if (strlen($hex) % 2) {
        $hex = '0' . $hex;
    }
    $lenBytes = hex2bin($hex);
    return chr(0x80 | strlen($lenBytes)) . $lenBytes;
}

function asn1_encode_integer(string $value): string {
    $value = ltrim($value, "\x00");
    if (strlen($value) === 0) {
        $value = "\x00";
    }
    if (ord($value[0]) > 0x7f) {
        $value = "\x00" . $value;
    }
    return "\x02" . asn1_encode_length(strlen($value)) . $value;
}

function asn1_encode_sequence(string $data): string {
    return "\x30" . asn1_encode_length(strlen($data)) . $data;
}

function asn1_encode_bitstring(string $data): string {
    return "\x03" . asn1_encode_length(strlen($data)) . $data;
}

function verify_registration_response(string $attestationObjectB64, string $clientDataJSONB64, string $expectedChallenge, string $rpId): ?array {
    $clientDataJSON = base64url_decode($clientDataJSONB64);
    $clientData = json_decode($clientDataJSON, true);
    if (!is_array($clientData) || ($clientData['type'] ?? '') !== 'webauthn.create') {
        return null;
    }
    if (!isset($clientData['challenge']) || $clientData['challenge'] !== $expectedChallenge) {
        return null;
    }

    $attestationObject = base64url_decode($attestationObjectB64);
    $offset = 0;
    $attObj = cbor_decode($attestationObject, $offset);
    if (!isset($attObj['authData'])) {
        return null;
    }

    $authData = parse_authenticator_data($attObj['authData']);
    if (empty($authData['attestedCredentialData'])) {
        return null;
    }

    $coseKey = $authData['attestedCredentialData']['publicKey'];
    $publicKeyPem = cose_key_to_pem($coseKey);
    if ($publicKeyPem === null) {
        return null;
    }

    return [
        'credentialId' => base64url_encode($authData['attestedCredentialData']['credentialId']),
        'publicKeyPem' => $publicKeyPem,
        'signCount' => $authData['signCount'],
    ];
}

function verify_authentication_response(string $authenticatorDataB64, string $clientDataJSONB64, string $signatureB64, string $expectedChallenge, string $rpId, string $publicKeyPem, int $previousSignCount): ?int {
    $clientDataJSON = base64url_decode($clientDataJSONB64);
    $clientData = json_decode($clientDataJSON, true);
    if (!is_array($clientData) || ($clientData['type'] ?? '') !== 'webauthn.get') {
        return null;
    }
    if (!isset($clientData['challenge']) || $clientData['challenge'] !== $expectedChallenge) {
        return null;
    }

    $authenticatorData = base64url_decode($authenticatorDataB64);
    $parsedData = parse_authenticator_data($authenticatorData);

    if (!hash_equals(hash('sha256', $rpId, true), $parsedData['rpIdHash'])) {
        return null;
    }
    if (!($parsedData['flags'] & 0x01)) {
        return null;
    }

    $signature = base64url_decode($signatureB64);
    $clientDataHash = hash('sha256', $clientDataJSON, true);
    $verificationData = $authenticatorData . $clientDataHash;

    $valid = openssl_verify($verificationData, $signature, $publicKeyPem, OPENSSL_ALGO_SHA256);
    if ($valid !== 1) {
        return null;
    }

    $signCount = $parsedData['signCount'];
    if ($signCount <= $previousSignCount && $previousSignCount !== 0) {
        return null;
    }

    return $signCount;
}
