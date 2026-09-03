@managing_tokens
Feature: Managing token prices
    In order to charge customers for what they use
    As an Administrator
    I want to define what each action costs in tokens

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Adding a token price
        When I want to create a new token price
        And I specify its code as "image_generation"
        And I name it "Image generation"
        And it costs 5 tokens
        And I add it
        Then I should be notified that it has been successfully created
        And the token price "Image generation" should appear in the list

    @ui
    Scenario: Refusing to add a token price with an already used code
        Given the store has a token price "Image generation" with code "image_generation" costing 5 tokens
        When I want to create a new token price
        And I specify its code as "image_generation"
        And I name it "Another action"
        And it costs 9 tokens
        And I add it
        Then I should be notified that the code must be unique
        And I browse token prices
        And I should see 1 token prices in the list

    @ui
    Scenario: Deleting a token price
        Given the store has a token price "Image generation" with code "image_generation" costing 5 tokens
        When I delete the token price "Image generation"
        Then I should be notified that it has been successfully deleted
        And the token price "image_generation" should no longer exist