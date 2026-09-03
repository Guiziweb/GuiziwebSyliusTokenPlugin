@managing_tokens
Feature: Adjusting a customer token balance
    In order to compensate a customer or fix a mistake
    As an Administrator
    I want to add or remove tokens from a customer wallet

    Background:
        Given the store operates on a single channel in "United States"
        And the store has customer "boyer.cruz@yahoo.com"
        And I am logged in as an administrator

    @ui
    Scenario: Adding tokens to a customer wallet
        Given the customer "boyer.cruz@yahoo.com" has "100" tokens
        When I want to adjust the token balance of "boyer.cruz@yahoo.com"
        And I add 250 tokens with the reason "Goodwill gesture"
        Then I should be notified that the tokens have been added
        And the customer "boyer.cruz@yahoo.com" should have "350" tokens

    @ui
    Scenario: Removing tokens from a customer wallet
        Given the customer "boyer.cruz@yahoo.com" has "100" tokens
        When I want to adjust the token balance of "boyer.cruz@yahoo.com"
        And I remove 40 tokens with the reason "Correction"
        Then I should be notified that the tokens have been removed
        And the customer "boyer.cruz@yahoo.com" should have "60" tokens

    @ui
    Scenario: Removing more tokens than the customer holds
        Given the customer "boyer.cruz@yahoo.com" has "30" tokens
        When I want to adjust the token balance of "boyer.cruz@yahoo.com"
        And I remove 100 tokens with the reason "Correction"
        Then I should be notified that the wallet does not hold enough tokens
        And the customer "boyer.cruz@yahoo.com" should have "30" tokens