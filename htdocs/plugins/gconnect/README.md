GConnect Module
===============

This module provides Google APIs OAuth 2 authentication client capabilities to Dolibarr. This module only works with existing and active user accounts in the Dolibarr system.


License
-------

Copyright (C) 2017 Multisistemas

GPLv3 or (at your option) any later version.

See COPYING for more information.


Module Prerequisites
--------------------

For Gconnect to work properly you must meet certain requirements:

1. User passwords must be explicitly stored in the database. To do this, log in as an administrator and go to:
	
	Setup -> Security -> Passwords -> Parameters
	Then disable the option: "Do no store clear passwords in database but store only encrypted value"

2. After this, a new password must be generated in Dolibarr for the users who will use the access with Google, in this way the new password will be saved in the database and Gconnect will be able to find it.


Install instructions
--------------------

1. You must first download the compressed package from the official site of DoliStore
2. You should then unpack the files in the folder intended for the installation of plugins. For example:
		
 /pathtodolibarr/htdocs/custom

This depends on what is specified on your conf.php variables:
 * $dolibarr_main_url_root_alt
 * $dolibarr_main_document_root_alt

3. That is all. You must now login as administrator to be able to configure the module.


Module configuration
--------------------

1. First you have to make sure to activate the module
2. Then in the configuration icon of the module, the configuration sections of the module appear.
3. Be sure to complete each of the configuration steps and fields described there, so that the module can function properly.


Update instructions
-------------------

The libraries used by the module:

1. Opauth - Multi-provider authentication framework for PHP ((C)2012 U-Zyn Chua)
2. ZeroClipboard ((C)2014 Jon Rohan, James M. Greene)

They can be updated manually using Composer, like this via console:

1. cd /pathtodolibarr/htdocs/plugins/gconnect
2. php pathto/composer.phar update

