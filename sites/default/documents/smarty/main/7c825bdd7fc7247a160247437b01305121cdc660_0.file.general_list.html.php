<?php
/* Smarty version 4.5.5, created on 2025-09-01 16:42:09
  from '/var/www/html/openemr/templates/insurance_numbers/general_list.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_68b6214152f809_04162771',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7c825bdd7fc7247a160247437b01305121cdc660' => 
    array (
      0 => '/var/www/html/openemr/templates/insurance_numbers/general_list.html',
      1 => 1742713071,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68b6214152f809_04162771 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/var/www/html/openemr/library/smarty/plugins/function.xlt.php','function'=>'smarty_function_xlt',),));
?>
<div class="table-responsive">
  <table class="table table-striped">
      <thead>
      <tr>
          <th><?php echo smarty_function_xlt(array('t'=>'Name'),$_smarty_tpl);?>
</th>
          <th>&nbsp;</th>
          <th><?php echo smarty_function_xlt(array('t'=>'Provider'),$_smarty_tpl);?>
 #</th>
          <th><?php echo smarty_function_xlt(array('t'=>'Rendering'),$_smarty_tpl);?>
 #</th>
          <th><?php echo smarty_function_xlt(array('t'=>'Group'),$_smarty_tpl);?>
 #</th>
      </tr>
      </thead>
      <tbody>
      <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['providers']->value, 'provider');
$_smarty_tpl->tpl_vars['provider']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['provider']->value) {
$_smarty_tpl->tpl_vars['provider']->do_else = false;
?>
      <tr>
          <td>
              <a href="<?php echo $_smarty_tpl->tpl_vars['CURRENT_ACTION']->value;?>
action=edit&id=default&provider_id=<?php echo attr_url($_smarty_tpl->tpl_vars['provider']->value->id);?>
" onclick="top.restoreSession()">
                  <?php echo text($_smarty_tpl->tpl_vars['provider']->value->get_name_display());?>

              </a>
          </td>
          <td><?php echo smarty_function_xlt(array('t'=>'Default'),$_smarty_tpl);?>
&nbsp;</td>
          <td><?php echo text($_smarty_tpl->tpl_vars['provider']->value->get_provider_number_default());?>
&nbsp;</td>
          <td><?php echo text($_smarty_tpl->tpl_vars['provider']->value->get_rendering_provider_number_default());?>
&nbsp;</td>
          <td><?php echo text($_smarty_tpl->tpl_vars['provider']->value->get_group_number_default());?>
&nbsp;</td>
      </tr>
      <?php
}
if ($_smarty_tpl->tpl_vars['provider']->do_else) {
?>
      <tr>
          <td><?php echo smarty_function_xlt(array('t'=>'No Providers Found'),$_smarty_tpl);?>
</td>
      </tr>
      <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
      </tbody>
  </table>
</div>
<?php }
}
