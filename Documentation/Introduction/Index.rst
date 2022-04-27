.. include:: /Includes.rst.txt

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

.. _introduction:

============
Introduction
============

Provides a module to easily configure main aspects from a website.

Once set up the module allows website owners without any TYPO3 knowledge to
configure main aspects from the website.

.. image:: ../Images/EditForm.jpg
   :alt: Easyconf edit form

Behind the scene the extension provides mappers to bind form fields with
TypoScript constants, site configuration or the configuration record from
the extension. To setup the edit form the following steps are involved:

#. Define the TCA
#. If needed create event handlers for advanced configurations

An example setup can be found in the commit
`aeae99b <https://github.com/buepro/typo3-pizpalue/commit/aeae99b5764394f0cb4bb827ae9198f5a3589f86>`__
from the extension pizpalue.
