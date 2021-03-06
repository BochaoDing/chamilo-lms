<?php
/* For license terms, see /license.txt */
use Chamilo\PluginBundle\Entity\ImsLti\ImsLtiTool;

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_admin_script();

if (!isset($_REQUEST['id'])) {
    api_not_allowed(true);
}

$toolId = intval($_REQUEST['id']);

$plugin = ImsLtiPlugin::create();
$em = Database::getManager();

/** @var ImsLtiTool $tool */
$tool = $em->find('ChamiloPluginBundle:ImsLti\ImsLtiTool', $toolId);

if (!$tool) {
    Display::addFlash(
        Display::return_message($plugin->get_lang('NoTool'), 'error')
    );

    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'ims_lti/admin.php');
    exit;
}

$form = new FormValidator('ims_lti_edit_tool');
$form->addText('name', get_lang('Name'));
$form->addText('url', $plugin->get_lang('LaunchUrl'));
$form->addText('consumer_key', $plugin->get_lang('ConsumerKey'));
$form->addText('shared_secret', $plugin->get_lang('SharedSecret'));
$form->addButtonAdvancedSettings('lti_adv');
$form->addHtml('<div id="lti_adv_options" style="display:none;">');
$form->addTextarea('description', get_lang('Description'), ['rows' => 3]);
$form->addTextarea('custom_params', [$plugin->get_lang('CustomParams'), $plugin->get_lang('CustomParamsHelp')]);
$form->addCheckBox('deep_linking', $plugin->get_lang('SupportDeepLinking'), get_lang('Yes'));
$form->addHtml('</div>');
$form->addButtonSave(get_lang('Save'));
$form->addHidden('id', $tool->getId());
$form->setDefaults([
    'name' => $tool->getName(),
    'description' => $tool->getDescription(),
    'url' => $tool->getLaunchUrl(),
    'consumer_key' => $tool->getConsumerKey(),
    'shared_secret' => $tool->getSharedSecret(),
    'custom_params' => $tool->getCustomParams(),
    'deep_linking' => $tool->isActiveDeepLinking(),
]);

if ($form->validate()) {
    $formValues = $form->exportValues();

    $tool
        ->setName($formValues['name'])
        ->setDescription($formValues['description'])
        ->setLaunchUrl($formValues['url'])
        ->setConsumerKey($formValues['consumer_key'])
        ->setSharedSecret($formValues['shared_secret'])
        ->setCustomParams($formValues['custom_params']);

    $em->persist($tool);
    $em->flush();

    Display::addFlash(
        Display::return_message($plugin->get_lang('ToolUpdated'), 'success')
    );

    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'ims_lti/admin.php');
    exit;
}

$template = new Template($plugin->get_lang('EditExternalTool'));
$template->assign('form', $form->returnForm());

$content = $template->fetch('ims_lti/view/add.tpl');

$template->assign('header', $plugin->get_title());
$template->assign('content', $content);
$template->display_one_col_template();
