<?php
/**
 * Helper functions for consistent photo handling across all front-end pages
 */

/**
 * Get the correct URL path for a profile photo
 * @param string|null $photoFilename The filename stored in the database
 * @param string $defaultInitial Single character for fallback avatar
 * @return array ['url' => string, 'hasImage' => bool, 'initial' => string]
 */
function getProfilePhotoUrl($photoFilename = null, $defaultInitial = 'U')
{
    $result = [
        'hasImage' => false,
        'url' => '',
        'initial' => strtoupper(substr($defaultInitial, 0, 1))
    ];

    if (!empty($photoFilename)) {
        // Normalize the filename (remove any path components)
        $cleanFilename = basename($photoFilename);
        // Use root-relative path that works from any directory
        $result['url'] = "/swaply/uploads/profiles/" . htmlspecialchars($cleanFilename);
        $result['hasImage'] = true;
    }

    return $result;
}

/**
 * Render a profile avatar image element with fallback
 * @param string|null $photoFilename The photo filename
 * @param string $name User's full name
 * @param string $class CSS classes to apply
 * @param bool $isClickable Whether the avatar should be clickable
 * @return string HTML for the avatar
 */
function renderProfileAvatar($photoFilename = null, $name = 'User', $class = 'w-12 h-12', $isClickable = false)
{
    $photoData = getProfilePhotoUrl($photoFilename, $name);
    $clickableAttr = $isClickable ? 'onclick="window.location.href=\'Profil.php\';" style="cursor:pointer;"' : '';

    if ($photoData['hasImage']) {
        return sprintf(
            '<img src="%s" alt="%s" class="%s rounded-full object-cover border border-teal-200" 
             onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';" %s>
            <div class="%s rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-lg border border-teal-200 hidden">%s</div>',
            $photoData['url'],
            htmlspecialchars($name),
            $class,
            $clickableAttr,
            $class,
            $photoData['initial']
        );
    } else {
        return sprintf(
            '<div class="%s rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-lg border border-teal-200" %s>%s</div>',
            $class,
            $clickableAttr,
            $photoData['initial']
        );
    }
}
?>
