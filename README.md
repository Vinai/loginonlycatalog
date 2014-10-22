Login Only Catalog
============================
Require a customer to be logged in order to view products and product collections.

Facts
-----
- version: check the [config.xml](https://github.com/Vinai/loginonlycatalog/blob/master/app/code/community/Netzarbeiter/LoginCatalog/etc/config.xml)
- extension key: Netzarbeiter_LoginCatalog
- extension on Magento Connect: -
- Magento Connect 1.0 extension key: -
- Magento Connect 2.0 extension key: -
- [extension on GitHub](https://github.com/Vinai/loginonlycatalog)
- [direct download link](https://github.com/Vinai/loginonlycatalog/zipball/master)

Description
-----------
This small extension requires a customer to be logged in order to view products or product collections.
If the customer navigates to a page that contains a product or a product collection, she will be redirected to the
account login page.
Alternatively the customer can be redirected to a configurable CMS page.

You can disable the module on a global, website or store level, and also choose to hide the category navigation in the
configuration.

This Module was designed to work with the Module [Netzarbeiter_CustomerActivation][], which
only allows customers to log in after they have been activated in the adminhtml interface.
There also is the [Netzarbeiter_GroupsCatalog2][] extension which allows you to hide Categories
and/or groups depending on the customers groups. Installing this extension together with
the Customer Groups Catalog Extension will probably make no sense, so you have to decide
what suits your needs better.

[Netzarbeiter_CustomerActivation]: https://github.com/Vinai/customer-activation "The CustomerActivation Extension on github"
[Netzarbeiter_GroupsCatalog2]: https://github.com/Vinai/groupscatalog2 "GroupsCatalog 2"

Compatibility
-------------
- Magento >= 1.1

Installation Instructions
-------------------------
1. Install the extension via Magento Connect with the key shown above or copy all the files into your document root.
2. Clear the cache, logout from the admin panel and then login again.
3. Configure and activate the extension under System - Configuration - Catalog - Login only catalog

Acknowledgements
----------------
Thanks to SeL for the french translation!
Thanks to kimpecov for the idea from http://www.magentocommerce.com/boards/viewthread/16743/

Support
-------
If you have any issues with this extension, open an issue on GitHub (see URL above)

Contribution
------------
Any contributions are highly appreciated. The best way to contribute code is to open a
[pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Vinai Kopp
[http://www.netzarbeiter.com](http://www.netzarbeiter.com)
[@VinaiKopp](https://twitter.com/VinaiKopp)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
Copyright (c) 2014 Vinai Kopp
