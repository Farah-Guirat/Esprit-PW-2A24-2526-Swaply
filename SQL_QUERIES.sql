-- ============================================
-- Requêtes SQL Utiles pour l'Administration
-- Double Authentification par Email - Swaply
-- ============================================

-- 1️⃣ VOIR LES TOKENS EN ATTENTE DE VÉRIFICATION
SELECT 
    id_token,
    email,
    token,
    created_at,
    expires_at,
    CASE 
        WHEN expires_at > NOW() THEN '✅ Valide'
        ELSE '❌ Expiré'
    END AS status
FROM email_verification_tokens
WHERE verified = 0
ORDER BY created_at DESC;

-- 2️⃣ VOIR LES TOKENS VÉRIFIÉS
SELECT 
    id_token,
    email,
    created_at,
    expires_at
FROM email_verification_tokens
WHERE verified = 1
ORDER BY created_at DESC;

-- 3️⃣ VOIR TOUS LES TOKENS
SELECT * FROM email_verification_tokens
ORDER BY created_at DESC;

-- 4️⃣ SUPPRIMER LES TOKENS EXPIRÉS
DELETE FROM email_verification_tokens 
WHERE expires_at < NOW();

-- 5️⃣ VOIR UN TOKEN SPÉCIFIQUE
SELECT 
    email,
    token,
    user_data,
    created_at,
    expires_at,
    verified
FROM email_verification_tokens
WHERE email = 'user@example.com'
LIMIT 1;

-- 6️⃣ VOIR LES UTILISATEURS AVEC EMAIL VÉRIFIÉ
SELECT 
    id_u,
    email,
    nom,
    prenom,
    email_verified,
    CASE 
        WHEN email_verified = 1 THEN '✅ Vérifié'
        ELSE '❌ Non vérifié'
    END AS verification_status
FROM utilisateurs
WHERE email_verified = 1
ORDER BY id_u DESC;

-- 7️⃣ VOIR LES UTILISATEURS SANS EMAIL VÉRIFIÉ
SELECT 
    id_u,
    email,
    nom,
    prenom,
    email_verified
FROM utilisateurs
WHERE email_verified = 0
ORDER BY id_u DESC;

-- 8️⃣ COMPTER LES TOKENS EN ATTENTE
SELECT COUNT(*) as tokens_en_attente
FROM email_verification_tokens
WHERE verified = 0 AND expires_at > NOW();

-- 9️⃣ COMPTER LES UTILISATEURS VÉRIFIÉS
SELECT COUNT(*) as utilisateurs_verifies
FROM utilisateurs
WHERE email_verified = 1;

-- 🔟 STATISTIQUES GLOBALES
SELECT 
    (SELECT COUNT(*) FROM email_verification_tokens WHERE verified = 0 AND expires_at > NOW()) as tokens_en_attente,
    (SELECT COUNT(*) FROM email_verification_tokens WHERE verified = 0 AND expires_at < NOW()) as tokens_expires,
    (SELECT COUNT(*) FROM email_verification_tokens WHERE verified = 1) as tokens_verifies,
    (SELECT COUNT(*) FROM utilisateurs WHERE email_verified = 1) as utilisateurs_verifies,
    (SELECT COUNT(*) FROM utilisateurs) as utilisateurs_total;

-- 1️⃣1️⃣ RESET POUR UN UTILISATEUR (supprimer son token en attente)
DELETE FROM email_verification_tokens 
WHERE email = 'user@example.com' AND verified = 0;

-- 1️⃣2️⃣ MARQUER UN EMAIL COMME VÉRIFIÉ MANUELLEMENT
UPDATE utilisateurs 
SET email_verified = 1 
WHERE email = 'user@example.com';

-- 1️⃣3️⃣ VOIR LES DONNÉES UTILISATEUR STOCKÉES DANS UN TOKEN
-- Note: user_data est en JSON, vous pouvez le voir directement
SELECT 
    email,
    user_data,
    JSON_EXTRACT(user_data, '$.firstname') as firstname,
    JSON_EXTRACT(user_data, '$.lastname') as lastname,
    JSON_EXTRACT(user_data, '$.phone') as phone,
    JSON_EXTRACT(user_data, '$.date_naissance') as date_naissance
FROM email_verification_tokens
WHERE email = 'user@example.com'
LIMIT 1;

-- 1️⃣4️⃣ VOIR LA STRUCTURE DE LA TABLE
DESCRIBE email_verification_tokens;

-- 1️⃣5️⃣ VOIR LES COLONNES DE LA TABLE UTILISATEURS
DESCRIBE utilisateurs;

-- 1️⃣6️⃣ SUPPRIMER TOUS LES TOKENS (⚠️ ATTENTION!)
DELETE FROM email_verification_tokens;

-- 1️⃣7️⃣ SUPPRIMER UN TOKEN SPÉCIFIQUE
DELETE FROM email_verification_tokens 
WHERE token = 'EXACT_TOKEN_HERE';

-- 1️⃣8️⃣ VOIR LES TOKENS PAR JOUR
SELECT 
    DATE(created_at) as date,
    COUNT(*) as nombre_tokens,
    SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verifies,
    SUM(CASE WHEN verified = 0 AND expires_at > NOW() THEN 1 ELSE 0 END) as en_attente
FROM email_verification_tokens
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- 1️⃣9️⃣ VOIR LES UTILISATEURS CRÉÉS RÉCEMMENT
SELECT 
    id_u,
    email,
    nom,
    prenom,
    email_verified,
    CASE 
        WHEN email_verified = 1 THEN 'Inscription complète'
        ELSE 'Email non vérifié'
    END as statut
FROM utilisateurs
ORDER BY id_u DESC
LIMIT 10;

-- 2️⃣0️⃣ RAPPORT DE VERIFICATION
SELECT 
    'Tokens en attente (valides)' as description,
    COUNT(*) as nombre
FROM email_verification_tokens
WHERE verified = 0 AND expires_at > NOW()
UNION ALL
SELECT 
    'Tokens expirés',
    COUNT(*)
FROM email_verification_tokens
WHERE verified = 0 AND expires_at < NOW()
UNION ALL
SELECT 
    'Tokens vérifiés',
    COUNT(*)
FROM email_verification_tokens
WHERE verified = 1
UNION ALL
SELECT 
    'Utilisateurs total',
    COUNT(*)
FROM utilisateurs
UNION ALL
SELECT 
    'Utilisateurs vérifiés',
    COUNT(*)
FROM utilisateurs
WHERE email_verified = 1;
