@managing_tokens
Feature: Browsing a customer token history
    In order to understand what happened on a wallet
    As an Administrator
    I want to read the ledger of a customer

    Background:
        Given the store operates on a single channel in "United States"
        And the store has customer "boyer.cruz@yahoo.com"
        And I am logged in as an administrator

    @ui
    Scenario: Reading the ledger of a wallet
        Given the customer "boyer.cruz@yahoo.com" has "100" tokens
        When I browse the token history of "boyer.cruz@yahoo.com"
        Then the wallet history should list 1 movements
        And the history should mention a "credit" movement

    @ui
    Scenario: An adjustment shows up in the ledger
        Given the customer "boyer.cruz@yahoo.com" has "100" tokens
        When I want to adjust the token balance of "boyer.cruz@yahoo.com"
        And I add 250 tokens with the reason "Goodwill gesture"
        And I browse the token history of "boyer.cruz@yahoo.com"
        Then the wallet history should list 2 movements
        And the history should mention a "Goodwill gesture" movement

    @ui
    Scenario: The token summary on the customer page
        Given the customer "boyer.cruz@yahoo.com" has "100" tokens
        When I want to adjust the token balance of "boyer.cruz@yahoo.com"
        And I remove 40 tokens with the reason "Correction"
        And I view the customer "boyer.cruz@yahoo.com"
        Then his token summary should read 60 available, 100 credited and 40 spent
