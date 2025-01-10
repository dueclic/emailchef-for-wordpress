<script>
	var urlToSettingsPage = <?php echo json_encode(admin_url('admin.php?page=emailchef')); ?>
</script>

<div class="ecf-main-container">
    <div class="ecf-main-account">
        <h2><?php _e("Emailchef Account", "emailchef"); ?></h2>
        <div class="ecf-account-status">
            <div class="ecf-account-connected"></div>
            <div><strong><?php _e("Account connected", "emailchef"); ?></strong></div>
        </div>
        <div class="ecf-account-info">
            <span class="flex-grow-1 truncate" title="alessandro@sendblaster.com"><?php echo $account->email; ?></span>
            <span>
                <a id="emailchef-disconnect" class="ecf-account-disconnect" title="<?php _e("Disconnect account", "emailchef"); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M280 24c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 240c0 13.3 10.7 24 24 24s24-10.7 24-24l0-240zM134.2 107.3c10.7-7.9 12.9-22.9 5.1-33.6s-22.9-12.9-33.6-5.1C46.5 112.3 8 182.7 8 262C8 394.6 115.5 502 248 502s240-107.5 240-240c0-79.3-38.5-149.7-97.8-193.3c-10.7-7.9-25.7-5.6-33.6 5.1s-5.6 25.7 5.1 33.6c47.5 35 78.2 91.2 78.2 154.7c0 106-86 192-192 192S56 368 56 262c0-63.4 30.7-119.7 78.2-154.7z"></path></svg>
                </a>
            </span>
        </div>
    </div>
    <div class="ecf-main-forms">
        <h2><?php _e("Website Forms", "emailchef"); ?></h2>
        <p><?php echo sprintf(__('Connect contact forms to automatically populate your <a target="_blank" href="%s">Emailchef</a> lists on every submission.', 'emailchef'),'https://emailchef.com/'); ?></p>
        <p><?php echo __('These are the contact forms found in your site:', 'emailchef'); ?></p>
        <?php
        Emailchef_Forms_Option::load();
        $formsDrivers = Emailchef_Drivers_Forms::getAll();
        $totFormNum = 0;
        foreach ($formsDrivers as $driver) {
            if (!$driver->isActive()) {
                continue;
            }
            $forms = $driver->getForms();
            if (count($forms) == 0) {
                continue;
            }
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
            <div class="notice notice-warning notice-alt">
                <p><?php _e("No forms found", "emailchef"); ?>/p>
            </div>
            <?php
        }
        ?>
    </div>
</div>
