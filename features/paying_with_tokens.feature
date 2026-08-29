@managing_tokens
Feature: Paying with tokens
    In order to use my tokens
    As a Customer
    I want to pay for consumable products with my token balance

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "CV generation" priced at "$0.00"
        And this product costs "5" tokens
        And the store ships everywhere for free
        And the store allows paying offline
        And the store has a payment method "Tokens" with a code "token" and token_wallet gateway
        And there is a customer account "jedidiah@example.com" identified by "password123"
        And I am logged in as "jedidiah@example.com"

    @ui
    Scenario: Spending tokens on a consumable product
        Given I have "20" tokens
        And I have product "CV generation" added to the cart
        And I have proceeded selecting "Tokens" payment method
        When I have confirmed order
        Then I should see the thank you page
        And I should have "15" tokens

    @ui
    Scenario: A consumable product cannot be bought without enough tokens
        Given I have "3" tokens
        And I have product "CV generation" added to the cart
        When I try to proceed through checkout process
        Then I should not be able to complete the order
