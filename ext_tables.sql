#
# Table structure for table 'tx_newsimporter_domain_model_importsource'
#
CREATE TABLE tx_newsimporter_domain_model_importsource (
	title varchar(255) DEFAULT '' NOT NULL,
	url varchar(255) DEFAULT '' NOT NULL,
	mapping text NOT NULL,

	disable_auto_import tinyint(4) unsigned DEFAULT '0' NOT NULL,
	last_run int(11) DEFAULT '0' NOT NULL,
	storage_pid int(11) DEFAULT '0' NOT NULL,
	image_folder varchar(255) DEFAULT '' NOT NULL,
	default_image int(11) DEFAULT '0' NOT NULL,

	filter tinytext,
	update_interval int(11) DEFAULT '0' NOT NULL
);

