@managing_tokens
Feature: Buying a token pack
    In order to use services paid with tokens
    As a Customer
    I want to buy tokens and have them credited to my wallet

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "Token pack" priced at "$100.00"
        And this product grants "500" tokens
        And the store ships everywhere for free
        And the store allows paying offline
        And there is a customer account "jedidiah@example.com" identified by "password123"
        And I am logged in as "jedidiah@example.com"

    @ui
    Scenario: Tokens are credited once the payment is completed
        Given I have product "Token pack" added to the cart
        And I have proceeded through checkout process
        And I have confirmed order
        When the order is paid
        Then I should have "500" tokens

    @ui
    Scenario: Tokens are not credited before the payment is completed
        Given I have product "Token pack" added to the cart
        When I proceed through checkout process
        Then I should have "0" tokens
