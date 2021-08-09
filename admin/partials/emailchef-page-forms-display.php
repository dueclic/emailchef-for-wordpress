<h1><?php _e("eMailChef Forms", "emailchef"); ?></h1>
<p><?php echo sprintf(__('Connect contact forms to automatically populate your <a target="_blank" href="%s">eMailChef</a> lists on every submission.', 'emailchef'),'http://emailchef.com/'); ?></p>
<p><?php echo __('These are the contact forms found in your site:', 'emailchef'); ?></p>
<script>
	var urlToSettingsPage = <?php echo json_encode(admin_url('admin.php?page=emailchef-options')); ?>
</script>
<?php
Emailchef_Forms_Option::load();
$formsDrivers = Emailchef_Drivers_Forms::getAll();
$totFormNum = 0;
foreach ($formsDrivers as $driver) {
    if (!$driver->isActive()) {
        continue;
    }
    $forms = $driver->getForms();
    ?>
    <div class="emailchef-form card accordion-container">
        <h2><?php echo $driver->getName() ?></h2>

        <?php
        foreach ($forms as $form) { $totFormNum++;
            ?>
            <div class="form control-section accordion-section
                <?php if (Emailchef_Forms_Option::isFormEnabled($driver, $form['id'])) { ?>active<?php } ?>"
                 data-id="<?php echo htmlentities($form['id'], ENT_QUOTES) ?>"
                 data-driver="<?php echo htmlentities($driver->getSlug(), ENT_QUOTES) ?>">
                <div class="accordion-section-title"
                     id="<?php echo esc_attr(sanitize_title($driver->getName()) . '-' . $form['id']);
                     ?>">
                    <?php echo $form['title'] ?>
                    <span class="not-connected"><?php echo __('warning: connection required!', 'emailchef'); ?></span>
                </div>
                <div class="accordion-section-content">
                    <div class="loading">
                        <?php echo __('Loading..', 'emailchef');
                        ?>
                    </div>
                    <div class="content">
                    </div>
                </div>
            </div>
            <?php

        }
        ?>
    </div>
    <?php
}

if(!$totFormNum){
	?>
	<p>No forms found</p>
	<?php
}

?>
