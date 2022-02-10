<?php
namespace Diveramkt\Widgetsociais;
// namespace diveramkt\whatsappfloat;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
	public function registerComponents()
	{
		return [
			'Diveramkt\Widgetsociais\Components\Instagram' => 'postsInstagram'
		];
		// return [
		// 	'diveramkt\whtasappfloat\components\Whatsapp' => 'Whatsapp'
		// ];
	}

	public function registerSettings()
	{
		return [
			'settings' => [
				'label'       => 'Widgetsociais',
				'description' => 'Pegar postagens dos widgets das redes sociais.',
				'category'    => 'DiveraMkt',
				'icon'        => 'icon-globe',
				'class'       => 'DiveraMkt\Widgetsociais\Models\Settings',
				'order'       => 500,
				'keywords'    => 'whatsapp link diveramkt',
				'permissions' => ['Widgetsociais.manage_widgetsociais']
			]
		];
	}
}
