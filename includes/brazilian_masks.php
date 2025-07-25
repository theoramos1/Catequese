<?php
require_once __DIR__ . '/../core/Configurator.php';
require_once __DIR__ . '/../core/domain/Locale.php';

// Import classes used to check the current locale
use catechesis\Configurator;
use core\domain\Locale;
// Include jQuery mask plugin and apply default Brazilian input masks when locale is BR

// Optionally specify $maskPathPrefix before including this file to adjust the
// relative path of the JS files. Default is an empty string which works for
// files in the project root.
if (!isset($maskPathPrefix)) {
    $maskPathPrefix = '';
}
?>
<?php if (Configurator::getConfigurationValueOrDefault(Configurator::KEY_LOCALIZATION_CODE) == Locale::BRASIL): ?>
<script src="<?= $maskPathPrefix ?>js/jQuery-Mask-Plugin-1.14.16/jquery.mask.min.js"></script>
<script src="<?= $maskPathPrefix ?>js/form-mask-utils.js"></script>
<script>
$(function(){
    applyBrazilianMasks();
});
</script>
<?php endif; ?>
