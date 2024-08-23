..  include:: /Includes.rst.txt

..  image:: https://poser.pugx.org/buepro/typo3-easyconf/v/stable.svg
    :alt: Latest Stable Version
    :target: https://extensions.typo3.org/extension/easyconf/

..  image:: https://img.shields.io/badge/TYPO3-13-orange.svg
    :alt: TYPO3 13
    :target: https://get.typo3.org/version/13

..  image:: https://img.shields.io/badge/TYPO3-12-orange.svg
    :alt: TYPO3 12
    :target: https://get.typo3.org/version/12

..  image:: https://poser.pugx.org/buepro/typo3-easyconf/d/total.svg
    :alt: Total Downloads
    :target: https://packagist.org/packages/buepro/typo3-easyconf

..  image:: https://poser.pugx.org/buepro/typo3-easyconf/d/monthly
    :alt: Monthly Downloads
    :target: https://packagist.org/packages/buepro/typo3-easyconf

..  image:: https://github.com/buepro/typo3-easyconf/workflows/CI/badge.svg
    :alt: Continuous Integration Status
    :target: https://github.com/buepro/typo3-easyconf/actions?query=workflow%3ACI

..  _introduction:

============
Introduction
============

This extension provides a module to show a website configuration form allowing
users without any technical knowledge to easily configure main aspects from a
website.

Behind the scene the extension provides mappers to bind form fields with
TypoScript constants, site configuration or the configuration record from
the extension. To setup the edit form the following steps are involved:

#.  Define the TCA
#.  If needed create event handlers for advanced configurations

An example setup can be found in the commit
`351c6af <https://github.com/buepro/typo3-pizpalue/commit/351c6af352ed195a325a153fcce7ecc723344de6>`__
from the extension pizpalue.

..  note::
    To setup the configuration form integrator or developer skills are required.

Screenshots
===========

..  figure:: ../Images/EditForm.jpg
    :class: with-shadow
    :alt: Website configuration form

    Website configuration form
