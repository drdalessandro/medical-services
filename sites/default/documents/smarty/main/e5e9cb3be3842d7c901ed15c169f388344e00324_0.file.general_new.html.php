<?php
/* Smarty version 4.5.5, created on 2025-08-26 15:33:00
  from '/var/www/html/openemr/interface/clickmap/template/general_new.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_68ae280c843ff5_85954235',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e5e9cb3be3842d7c901ed15c169f388344e00324' => 
    array (
      0 => '/var/www/html/openemr/interface/clickmap/template/general_new.html',
      1 => 1754668171,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68ae280c843ff5_85954235 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/var/www/html/openemr/library/smarty/plugins/function.headerTemplate.php','function'=>'smarty_function_headerTemplate',),1=>array('file'=>'/var/www/html/openemr/library/smarty/plugins/function.xl.php','function'=>'smarty_function_xl',),));
if (!$_smarty_tpl->tpl_vars['reportMode']->value) {?>
    <html>
    <head>
    <?php echo smarty_function_headerTemplate(array(),$_smarty_tpl);?>

<?php }
echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['GLOBALS']->value['webroot'];?>
/library/js/clickmap.js"><?php echo '</script'; ?>
>
<link rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['form']->value->template_dir;?>
/css/clickmap.css" />
<?php if (!$_smarty_tpl->tpl_vars['reportMode']->value) {?>
	</head>
	<body>
<?php }?>
<div class="container">
    <div class="row">
        <div class="col-9">
            <div id="container" class="container graphic-pain-map">
                <img src="<?php echo $_smarty_tpl->tpl_vars['form']->value->image;?>
"/>
            </div>
        </div>
        <div class="col-3">
            <div id="legend" class="legend graphic-pain-map">
                <div class="body">
                    <ul></ul>
                </div>
            </div>
        </div>
        <?php if (!$_smarty_tpl->tpl_vars['reportMode']->value) {?>
            <div class="col-12">
                <div class="btn-group">
                    <button class="btn btn-primary btn-save" id="btn_save"><?php echo smarty_function_xl(array('t'=>"Save"),$_smarty_tpl);?>
</button>
                    <button class="btn btn-secondary btn-delete" id="btn_clear"><?php echo smarty_function_xl(array('t'=>"Clear"),$_smarty_tpl);?>
</button>
                    <button class="btn btn-secondary btn-cancel" onclick="top.restoreSession(); location.href='javascript:parent.closeTab(window.name, false)'"><?php echo smarty_function_xl(array('t'=>"Cancel"),$_smarty_tpl);?>
</button>
                </div>
                <p>
                    <?php echo smarty_function_xl(array('t'=>"Click a spot on the graphic to add a new annotation, click it again to remove it"),$_smarty_tpl);?>
 <br/>
                    <?php echo smarty_function_xl(array('t'=>"The 'Clear' button will remove all annotations."),$_smarty_tpl);?>

                </p>
            </div>
        <?php }?>
    </div>
</div>

<div class="marker-template d-none">
	<span class='count'></span>
</div>

<?php echo '<script'; ?>
>

    $(function () {
		var optionsLabel = <?php echo $_smarty_tpl->tpl_vars['form']->value->optionsLabel;?>
;
		var options = <?php echo $_smarty_tpl->tpl_vars['form']->value->optionList;?>
;
		var data = <?php echo $_smarty_tpl->tpl_vars['form']->value->data;?>
;
		var hideNav = <?php echo $_smarty_tpl->tpl_vars['form']->value->hideNav;?>
;

		clickmap({
            hideNav: hideNav,
            data: data,
			dropdownOptions: { label: optionsLabel, options: options },
			container: $("#container")
		});
	});

<?php echo '</script'; ?>
>

<?php if (!$_smarty_tpl->tpl_vars['reportMode']->value) {?>
    <form id="submitForm" name="submitForm" method="post" action="<?php echo $_smarty_tpl->tpl_vars['form']->value->saveAction;?>
" onsubmit="return top.restoreSession()">
        <input type="hidden" name="id" value="<?php echo $_smarty_tpl->tpl_vars['form']->value->get_id();?>
" />
        <input type="hidden" name="pid" value="<?php echo $_smarty_tpl->tpl_vars['form']->value->get_pid();?>
" />
        <input type="hidden" name="process" value="true" />
        <input type="hidden" name="data" id="data" value=""/>
    </form>
    </body>
    </html>
<?php }
}
}
