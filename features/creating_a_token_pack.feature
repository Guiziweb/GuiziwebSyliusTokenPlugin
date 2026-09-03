@managing_tokens
Feature: Creating a token pack
    In order to sell tokens
    As an Administrator
    I want to create a product that credits tokens and never ships

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: A token pack is created as a dematerialised product
        When I want to create a new token pack
        Then the shipping fields should not be available
        When I set its code to "STARTER_PACK"
        And I call it "Starter pack"
        And it grants 100 tokens
        And it costs "$19.99" in the "United States" channel
        And I create it
        Then the token pack "STARTER_PACK" should never require shipping
        And the token pack "Starter pack" should appear in the list

    @ui
    Scenario: A token pack is edited to grant more tokens with a validity
        When I want to create a new token pack
        And I set its code to "STARTER_PACK"
        And I call it "Starter pack"
        And it grants 100 tokens
        And it costs "$19.99" in the "United States" channel
        And I create it
        When I want to edit the token pack "STARTER_PACK"
        And I change the granted tokens to 250
        And I set its validity to 6 months
        And I save my changes
        Then the token pack "STARTER_PACK" should grant 250 tokens valid for 6 months