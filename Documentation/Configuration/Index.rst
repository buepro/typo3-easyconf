.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

TCA
===

The extension adds the column property `tx_easyconf`:

.. code-block:: php

   $GLOBALS['TCA']['tx_easyconf_configuration']['columns'][$field]['tx_easyconf']

It can hold the following properties:

.. confval:: mapAlways

   :Required: false
   :data type: boolean
   :Scope: TypoScript constants
   :Path: $GLOBALS > TCA > tx_easyconf_configuration > [field] > tx_easyconf

   Writes the property value obtained from the field always to the typoscript
   file, even if the inherited value is equal. The normal behaviour is to write
   only changed property values to the file. This feature is useful where
   property values depend on conditions. In such cases the inherited value
   doesn't necessarily reflect the real value. As an example assume a menu
   property having a different value on subpages.

.. confval:: mapper

   :Required: true
   :data type: string
   :Scope: Easyconf mappers
   :Path: $GLOBALS > TCA > tx_easyconf_configuration > [field] > tx_easyconf

   Defines the class used to map the field value to the corresponding
   configuration.

.. confval:: path

   :Required: true
   :data type: string
   :Scope: Easyconf mappers
   :Path: $GLOBALS > TCA > tx_easyconf_configuration > [field] > tx_easyconf

   Defines the property path used my the mapper to read and write the
   corresponding configuration.
