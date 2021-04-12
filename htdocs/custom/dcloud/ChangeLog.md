# Dropbox ChangeLog


## 2.5.1

FIX left menu of Dropbox GED not updated  
FIX compatibility of encryption from PHP 5.6 to 7.2

## 2.5.0

NEW add Suppliers Proposals module synchro  
NEW add Suppliers Payments module synchro  
NEW compatibility with files renaming  
FIX compatibility with Dolibarr 7.0  
FIX compatibility with PHP 7.2  
FIX Invoice supplier synchro  
FIX remove old upgrade process

## 2.4.0

Fix: compatibility with Dropbox API v2  
Warning: PHP 5.6.4 or greater is required

## 2.3.2

Fix: compatibility with Dolibarr 6.0  
Fix: add jquery layout library (removed in Dolibarr 6)

## 2.3.1

New: add Agenda module synchro  
New: add Resource module synchro  
Fix: compatibility with Dolibarr 5.0

## 2.3.0

New: add possibility to show/hide native and Dropbox tabs  
Fix: compatibility with php7

## 2.2.1

Fix: compatibility with Dolibarr 4.0  
Fix: add more tests for roots directories

## 2.2.0

Fix: compatibility with Dolibarr 3.8/3.9  
Fix: jquery "live" method is deprecated  
Fix: upgrade Dropbox PHP core SDK (version 1.1.6) 

## 2.1.3

Fix: avoid conflict with others modules  
New: change the development platform  
New: add Transifex management

## 2.1.2

New: add compatibility with DOT and DOCX documents

## 2.1.1

Fix: use "afterPDFCreation" hook instead builddoc trigger

## 2.1.0

New: enhanced users rights  
New: external users can see documents of his company  
Fix: SSL certificat verify peer problem with Wampserver  
Fix: compatibility with old and new path of photos (Dolibarr 3.7)  
Fix: compatibility with Dolibarr 3.7

## 2.0.1

Fix: Dropbox use TLS instead of SSLv3 (SSLv3 has security issues).  
Fix: adding a warning message if the server is 32bit

## 2.0.0

Fix: compatibility with Dolibarr 3.5.x and 3.6.x  
New: use Dropbox OAuth2 instead OAuth1 (more secure)  
New: show directories in main window  
New: add compatibility with some modules (internal and external)  
New: add Dropbox tab in products and services card  
New: add different customer/supplier tabs in Dropbox thirdparty tab  
New: add compatibility with Memcached and Shmop (more speed)  
New: add synchronization and migration of existing files

## 1.2.4

Fix: compatibility with new hookmanager method

## 1.2.3

Fix: broken features with fileupload class

## 1.2.2

Fix: problem with order supplier synchronisation  
Fix: compatibility with multicompany

## 1.2.1

Fix: some regression in Dolibarr (again, and again)

## 1.2.0

New: upgrade with GPLv3 license  
Fix: compatibility with Dolibarr 3.3+ due to several regressions  
Fix: add jQuery jstree

## 1.1.0

New: add spanish translation  
Fix: rename dropbox to dcloud  
Fix: rename trigger  
Fix: compatibility with the latest jQuery FileUpload  
Fix: delete files and directory if object is deleted  
Fix: select parent directory if file is selected  
Fix: problem with IE  
Fix: remove obsolete code

## 1.0.0

First release of this module
