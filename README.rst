.. image:: https://poser.pugx.org/buepro/typo3-easyconf/v/stable.svg
   :alt: Latest Stable Version
   :target: https://extensions.typo3.org/extension/easyconf/

.. image:: https://img.shields.io/badge/TYPO3-11-orange.svg
   :alt: TYPO3 11
   :target: https://get.typo3.org/version/11

.. image:: https://poser.pugx.org/buepro/typo3-easyconf/d/total.svg
   :alt: Total Downloads
   :target: https://packagist.org/packages/buepro/typo3-easyconf

.. image:: https://poser.pugx.org/buepro/typo3-easyconf/d/monthly
   :alt: Monthly Downloads
   :target: https://packagist.org/packages/buepro/typo3-easyconf

.. image:: https://github.com/buepro/typo3-easyconf/workflows/CI/badge.svg
   :alt: Continuous Integration Status
   :target: https://github.com/buepro/typo3-easyconf/actions?query=workflow%3ACI

============================
TYPO3 extension ``easyconf``
============================

This extension provides a module to show a website configuration form allowing
users without any technical knowledge to easily configure main aspects from a
website.

Behind the scene the extension provides mappers to bind form fields with
TypoScript constants, site configuration or the configuration record from
the extension. To setup the edit form the following steps are involved:

#. Define the TCA
#. If needed create event handlers for advanced configurations

An example setup can be found in the commit
`29d47cc4 <https://github.com/buepro/typo3-pizpalue/commit/29d47cc4d6a27da66fecd947a6751862f9dca77e>`__
from the extension pizpalue.

.. note::
   To setup the configuration form integrator or developer skills are required.

:Repository:  https://github.com/buepro/typo3-easyconf
:Read online: https://docs.typo3.org/p/buepro/typo3-easyconf/main/en-us/
:TER:         https://extensions.typo3.org/extension/easyconf

Screenshots
===========

.. figure:: ../Images/EditForm.jpg
   :class: with-shadow
   :alt: Website configuration form

   Website configuration form
