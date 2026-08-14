<?php require APP_ROOT . '/app/Views/layouts/public_header.php'; ?>
<?= $content ?>
<?php if (($showCampaignPopup ?? false) === true): ?>
    <?php require APP_ROOT . '/app/Views/layouts/public_campaign_popup.php'; ?>
<?php endif; ?>
<?php require APP_ROOT . '/app/Views/layouts/public_footer.php'; ?>
