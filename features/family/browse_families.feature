Feature: Browse families
  In order to view the families that have been created
  As a user
  I need to be able to view a list of them

  Scenario: Successfully display all the families
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    When I am on the families page
    Then the grid should contain 3 elements
    And I should see the columns Code, Label and Label as attribute
    And I should see families Boots, Sandals and Sneakers
    And the rows should be sorted ascending by code