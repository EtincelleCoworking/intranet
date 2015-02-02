Feature: Invoice
As an SuperAdmin I must be use the invoice system

Scenario:
    Given I am "SuperAdmin"
    When I go to the "Liste des factures"
    Then I must see all invoices