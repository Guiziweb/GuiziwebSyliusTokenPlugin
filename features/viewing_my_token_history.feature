@managing_tokens
Feature: Viewing my token history
    In order to know what I have left and where my tokens went
    As a Customer
    I want to see my balance and my token movements

    Background:
        Given the store operates on a single channel in "United States"
        And there is a customer account "boyer.cruz@yahoo.com" identified by "password123"
        And I am logged in as "boyer.cruz@yahoo.com"

    @ui
    Scenario: Seeing my balance and my movements
        Given I have "500" tokens
        When I browse my token history
        Then my displayed token balance should be 500
        And I should see 1 token movements
        And I should see a token movement labelled "Credit"

    @ui
    Scenario: A customer without tokens sees an empty history
        When I browse my token history
        Then my displayed token balance should be 0
        And I should see 0 token movements