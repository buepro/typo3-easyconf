.. include:: /Includes.rst.txt

.. _usingMappers:

=============
Using mappers
=============

**Audience:** Integrators, Developers

A form value can be mapped to a TypoScript constant, a site configuration
property or a field from the configuration table from this extension. The
mapping is defined in a php file located under `Configuration/TCA/Overrides`
and assisted by helper functions from the extension `TcaUtility` class.

Basic TCA-file structure
========================

.. code-block:: php

   <?php

   use Buepro\Easyconf\Mapper\EasyconfMapper;
   use Buepro\Easyconf\Mapper\SiteConfigurationMapper;
   use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
   use Buepro\Easyconf\Utility\TcaUtility;

   defined('TYPO3') or die('Access denied.');

   if (!isset($GLOBALS['TCA']['tx_easyconf_configuration'])) {
      return;
   }

   (static function () {
       $l10nFile = 'LLL:EXT:site_package/Resources/Private/Language/locallang_db.xlf';
       $tca = &$GLOBALS['TCA']['tx_easyconf_configuration'];

       /**
        * Define columns
        */
       $propertyMaps = [
           // ***************************
           // * Here we use the mappers *
           // ***************************
       ];
       $tca['columns'] = TcaUtility::getColumns($propertyMaps, $l10nFile);

       /**
        * Define palettes
        */
       // ...

       /**
        * Modify columns
        */
       // ...

       /**
        * Define type (tabs from the form with palettes and fields)
        */
       // ...

       unset($tca);
   })();

.. note::
   Replace **site_package** with the key from your extension key.

TypoScript constant mapper
==========================

Description
-----------

On each page where a template record is available TypoScript constants can be
altered through form fields. This is achieved by creating a template related
file holding the constant definitions and importing this file through the
template constants definition.

.. note::
   The inheritance hierarchy from TypoScript is maintained meaning if you set
   a field value on the root page it can be overwritten on subpages (if a
   template record exists for that subpage).

.. note::
   The way the TypoScript file import is maintained in the template constants
   field can be adjusted by the TypoScript constant
   `module.tx_easyconf.settings.typoScriptConstantMapper.importStatementHandling`.

Definition
----------

.. code-block:: php

   /**
   * Define columns
   */
   $propertyMaps = [
       TcaUtility::getPropertyMap(
           TypoScriptConstantMapper::class,
           'easyconf.demo',
           'company, domain, firstName, lastName',
           'owner'
       ),
   ]

Mapping result
--------------

========================== =====================================================
Form field                 TypoScript constant
========================== =====================================================
owner_company              easyconf.demo.company
owner_domain               easyconf.demo.domain
owner_first_name           easyconf.demo.firstName
owner_last_name            easyconf.demo.lastName
========================== =====================================================

Site configuration mapper
=========================

Description
-----------

This mapper relates form fields with the site configuration hence writes and
reads from the site configuration yaml file.

.. note::
   Since these fields have site scope a change on one page is shown on all
   other pages having a template record meaning no hierarchical configuration
   is possible as known from the TypoScript mapping.

Definition
----------

.. code-block:: php

   /**
   * Define columns
   */
   $propertyMaps = [
       TcaUtility::getPropertyMap(
           SiteConfigurationMapper::class,
           'easyconf.data.demo',
           'company, contact, email, phone',
           'agency'
       ),
   ]

Mapping result
--------------

========================== =====================================================
Form field                 Site configuration property
========================== =====================================================
agency_company             easyconf.data.demo.company
agency_contact             easyconf.data.demo.contact
agency_email               easyconf.data.demo.email
agency_phone               easyconf.data.demo.phone
========================== =====================================================

Easyconf mapper
===============

Description
-----------

This mapper relates form fields with the field `fields` from the table
`tx_easyconf_configuration`. For each page a template record exists a
record is created in the table `tx_easyconf_configuration`. As a result
these form values have a template scope.

Definition
----------

.. code-block:: php

   /**
   * Define columns
   */
   TcaUtility::getPropertyMap(
       EasyconfMapper::class,
       'demo',
       'showAllProperties',
       'easyconf'
   ),

Mapping result
--------------

========================== =====================================================
Form field                 Path from `fields` array
========================== =====================================================
easyconf_demo              demo.showAllProperties
========================== =====================================================

Complete code
=============

.. code-block:: php

   <?php

   use Buepro\Easyconf\Mapper\EasyconfMapper;
   use Buepro\Easyconf\Mapper\SiteConfigurationMapper;
   use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
   use Buepro\Easyconf\Utility\TcaUtility;

   defined('TYPO3') or die('Access denied.');

   if (!isset($GLOBALS['TCA']['tx_easyconf_configuration'])) {
       return;
   }

   (static function () {
       $l10nFile = 'LLL:EXT:site_package/Resources/Private/Language/locallang_db.xlf';
       $tca = &$GLOBALS['TCA']['tx_easyconf_configuration'];

       /**
        * Define columns
        */
       $propertyMaps = [
           TcaUtility::getPropertyMap(
               TypoScriptConstantMapper::class,
               'easyconf.demo',
               'company, domain, firstName, lastName',
               'owner'
           ),
           TcaUtility::getPropertyMap(
               SiteConfigurationMapper::class,
               'easyconf.data.demo',
               'company, contact, email, phone',
               'agency'
           ),
           TcaUtility::getPropertyMap(
               EasyconfMapper::class,
               'demo',
               'showAllProperties',
               'easyconf'
           ),
       ];
       $tca['columns'] = TcaUtility::getColumns($propertyMaps, $l10nFile);

       /**
        * Define palettes
        */
       $tca['palettes'] = [
           'paletteCompany' => TcaUtility::getPalette(
               'company, domain',
               'owner'
           ),
       ];

       /**
        * Modify columns
        */
       TcaUtility::modifyColumns(
           $tca['columns'],
           'showAllProperties',
           [
               'onChange' => 'reload',
               'config' => ['type' => 'check', 'renderType' => 'checkboxToggle'],
           ],
           'easyconf'
       );
       TcaUtility::modifyColumns(
           $tca['columns'],
           'firstName, lastName',
           ['displayCond' => 'FIELD:easyconf_show_all_properties:REQ:true'],
           'owner'
       );

       /**
        * Define type (tabs from the form with palettes and fields)
        */
       $tabs = [
           'tabTypoScript' => implode(', ', [
               '--palette--;;paletteCompany',
               TcaUtility::getFieldList('firstName, lastName', 'owner'),
           ]),
           'tabSiteConfiguration' => TcaUtility::getFieldList('company, contact, email, phone', 'agency'),
           'tabEasyconf' => TcaUtility::getFieldList('showAllProperties', 'easyconf'),
       ];
       $tca['types'][0] = TcaUtility::getType($tabs, $l10nFile);

       unset($tca);
   })();
