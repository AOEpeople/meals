import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.dishes.overview", () => {
  const checkDishEditingElements = (user: string) => {
    // log user in
    login(user);

    // open dishes
    cy.get("ul[class='navbar']").find("a[href='/dish']").click();

    // create new dish
    cy.get("[href='/dish/form']").click();

    // check visibility of elements
    cy.get("[class='create-form top-form']").should("be.visible");

    cy.get("input[id='dish_title_de").should("be.visible").as("titleDE");
    cy.get("input[id='dish_description_de")
      .should("be.visible")
      .as("descriptionDE");

    cy.get("input[id='dish_title_en").should("be.visible").as("titleEN");
    cy.get("input[id='dish_description_en")
      .should("be.visible")
      .as("descriptionEN");

    cy.get("select[id='dish_category").should("be.visible").as("category");

    cy.get("button[id='dish_save").should("be.visible").as("saveAction");
  };

  const checkDishEditingFunctions = () => {
    // save dish
    cy.get("@titleDE").type("titleDE");
    cy.get("@descriptionDE").type("descriptionDE");
    cy.get("@titleEN").type("titleEN");
    cy.get("@descriptionEN").type("descriptionEN");
    cy.get("@category").select("Others");

    cy.get("@saveAction").click();
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been added.");

    // check visibility of elements
    cy.get("a[href='/dish/form/titleen").should("be.visible").as("editAction");
    cy.get("a[href='/dish/titleen/delete")
      .should("be.visible")
      .as("deleteAction");

    // edit dish
    cy.get("@editAction").click();
    cy.get("@saveAction").click();
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been modified.");

    // delete dish
    cy.get("@deleteAction").click();
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been deleted.");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkDishEditingElements(data.user.kochomi);
    checkDishEditingFunctions();
  });
});
