<form action='options.php' method='post'>

    <h1><?php _e("Settings", "emailchef"); ?></h1>

    <?php
    settings_fields('pluginPage');
    do_settings_sections('pluginPage');

    ?>
    <button id="emailchef-check-login" class="button"><?php echo __('Test Login', 'emailchef'); ?></button>
    <span class="emailchef-check-login-result"></span>
    <?php

    submit_button();
    ?>

</form>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var emailChefTesting = false;
        $('input[name="emailchef_settings\[emailchef_email\]"],input[name="emailchef_settings\[emailchef_password\]"]').change(function () {
            $('.emailchef-check-login-result').removeClass('error valid').text('');
            $('input[name="emailchef_settings\[emailchef_email\]"],input[name="emailchef_settings\[emailchef_password\]"]').removeClass('error valid');
        });
        $('#emailchef-check-login').click(function (e) {
            e.preventDefault();
            if (emailChefTesting) {
                return;
            }
            $('.emailchef-check-login-result').removeClass('error valid').text('');
            $('input[name="emailchef_settings\[emailchef_email\]"],input[name="emailchef_settings\[emailchef_password\]"]').removeClass('error valid');
            emailChefTesting = true;
            var that = this;
            $(this).text(<?php echo json_encode(__('Please Wait..', 'emailchef')); ?>).addClass('disabled');
            eMailChef.checkLogin(
                $('input[name="emailchef_settings\[emailchef_email\]"]').val(),
                $('input[name="emailchef_settings\[emailchef_password\]"]').val(),
                function (res) {
                    emailChefTesting = false;
                    $(that).text(<?php echo json_encode(__('Test Login', 'emailchef')); ?>).removeClass('disabled');
                    if (res) {
                        $('.emailchef-check-login-result').addClass('valid').text(<?php echo json_encode(__('Login correct!', 'emailchef')); ?>);
                        $('input[name="emailchef_settings\[emailchef_email\]"],input[name="emailchef_settings\[emailchef_password\]"]').addClass('valid');
                    } else {
                        $('.emailchef-check-login-result').addClass('error').text(<?php echo json_encode(__('Login failed', 'emailchef')); ?>);
                        $('input[name="emailchef_settings\[emailchef_email\]"],input[name="emailchef_settings\[emailchef_password\]"]').addClass('error');
                    }
                }
            )
        })
    });
</script>
