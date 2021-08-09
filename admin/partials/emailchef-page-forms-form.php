<form>
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row"><label
                    for="<?php echo htmlentities($driverName, ENT_QUOTES) ?>-<?php echo htmlentities($id, ENT_QUOTES) ?>-list"><?php echo __('eMailChef List:', 'emailchef') ?></label>
            </th>
            <td>
                <p class="warning-select-list"><?php echo __('Select the list and save.', 'emailchef') ?></p>
                <select name="listId" class="list-id"
                        id="<?php echo htmlentities($driverName, ENT_QUOTES) ?>-<?php echo htmlentities($id, ENT_QUOTES) ?>-list">
                    <option value="">-</option>
                    <?php foreach ($formData['lists'] as $list) {
                        ?>
                        <option value="<?php echo $list->id ?>" <?php if ($list->id == $formData['listId']) {
                            echo 'selected';
                        }
                        ?>><?php echo htmlentities($list->name, ENT_QUOTES) ?></option>
                        <?php

                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php echo __('Map', 'emailchef') ?></th>
            <td>
                <div class="map-reload">
                    <p><?php echo __('Save to reload list fields', 'emailchef') ?></p>
                </div>
                <div class="content-map">
                    <p><?php echo __('Map form fields with eMailChef List fields. <br><em class="at-least-email">At least an email field needs to be mapped to enable the connection.</em>', 'emailchef') ?></p>
                    <p><?php echo __('Remember to save your changes.', 'emailchef') ?></p>
                    <div class="form-table-container">
                        <table class="form-table">
                            <thead>
                            <tr>
                                <th><?php echo __('Form field', 'emailchef') ?></th>
                                <th><?php echo __('eMailChef List field', 'emailchef') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($formData['formFields'] as $field) {
                                ?>
                                <tr data-id="<?php echo htmlentities($field['id'], ENT_QUOTES) ?>">
                                    <td class="_nopadding">
                                        <?php echo htmlentities($field['title'], ENT_QUOTES) ?>
                                        <?php if($field['error']){ ?>
                                          <small class="error-field"> [ <?php echo htmlentities($field['error'], ENT_QUOTES) ?> ] </small>

                                        <?php } ?>
                                    </td>
                                    <td class="_nopadding">
                                        <select name="field[<?php echo htmlentities($field['id'], ENT_QUOTES) ?>]" <?php if($field['error']){ ?>disabled<?php } ?>>
                                            <option value="">-</option>
                                            <?php if(!$field['error']){
                                             foreach ($formData['listFields'] as $field2) {
                                                ?>
                                                <option
                                                    value="<?php echo htmlentities($field2['id'], ENT_QUOTES) ?>" <?php if (isset($formData['savedFields'][$field['id']]) && $formData['savedFields'][$field['id']] == $field2['id']) {
                                                    echo 'selected';
                                                }
                                                ?>><?php echo htmlentities($field2['title'], ENT_QUOTES) ?></option>
                                                <?php
                                              }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php

                            } ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="auto-create">
                    <button
                        class="button _right create"><?php echo __('Create and map unmapped fields automatically', 'emailchef') ?></button>
                      </p>
                </div>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
              <button class="button _right reset"><?php echo __('Reset', 'emailchef') ?></button>
                <input type="submit" class="button button-primary _right save"
                       value="<?php echo __('Save', 'emailchef') ?>"/>
            </td>
        </tr>
        </tbody>
    </table>
</form>
