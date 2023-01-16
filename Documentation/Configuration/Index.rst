..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============

Support information
===================

When closing the form editor an info panel is shown providing agency related
links. The links can be defined in the site configuration:

..  code-block:: yaml

    easyconf:
      data:
        admin:
          agency:
            email: bh@agency.ch
            phone: '111 111 11 11'
            url: 'https://www.agency.ch'

TCA
===

The extension adds the column property `tx_easyconf`:

..  code-block:: php

    $GLOBALS['TCA']['tx_easyconf_configuration']['columns'][$field]['tx_easyconf']

It can hold the following properties:

..  confval:: mapAlways

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

..  confval:: mapper

    :Required: true
    :data type: string
    :Scope: Easyconf mappers
    :Path: $GLOBALS > TCA > tx_easyconf_configuration > [field] > tx_easyconf

    Defines the class used to map the field value to the corresponding
    configuration.

..  confval:: path

    :Required: true
    :data type: string
    :Scope: Easyconf mappers
    :Path: $GLOBALS > TCA > tx_easyconf_configuration > [field] > tx_easyconf

    Defines the property path used my the mapper to read and write the
    corresponding configuration.

TypoScript constants substitution
=================================

Starting with TYPO3 v12 TypoScript constants aren't substituted any more: The
assignment :typoscript:`b = {$a}` with :typoscript:`a = test` results in
:typoscript:`b = {$a}` and not in :typoscript:`b = test`. Especially when
configuring various extensions it would be faster to just set some global
constants and assign them accordingly. Here the TypoScript substitution feature
comes into play:

..  code-block:: typoscript

    easyconf.substitutions {
      someExtenstion {
        domain = {$globals.customer.domain}
        dateFormat = {$globals.general.dateFormat}
      }
      someOtherExtension {
        link = <a href="https://www.{$globals.customer.domain}">{$globals.customer.company}</a>
      }
    }

The above TypoScript constant definition would result in:

..  code-block:: typoscript

    someExtension {
        domain = domain.ch
        dateFormat = j. F Y
    }
    someOtherExtension {
        link = <a href="https://www.domain.ch">Company GmbH</a>
    }

As demonstrated all constants appearing on the right side from the equal (=)
sign become substituted.
