<?php
return [
	'default' => 'local',
	'cloud' => 's3',
	'disks' => [
		'local' => [
			'driver' => 'local',
			'root'   => storage_path().'/app',
		],
		's3' => [
			'driver' => 's3',
			'key'    => 'your-key',
			'secret' => 'your-secret',
			'region' => 'your-region',
			'bucket' => 'your-bucket',
		],
		'rackspace' => [
			'driver'    => 'rackspace',
			'username'  => 'your-username',
			'key'       => 'your-key',
			'container' => 'your-container',
			'endpoint'  => 'https:
			'region'    => 'IAD',
			'url_type'  => 'publicURL'
		],
	],
];
