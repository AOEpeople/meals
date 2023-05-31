describe("Test TimeSlots View", () => {
  beforeEach(() => {
    cy.setCookie("locale", "de");
    cy.loginAs("kochomi");
    cy.visitMeals();
  });

  it("should be able to navigate to '/time-slots' and have the header displayed", () => {
    cy.visit("/time-slots");

    cy.get("h2").should((ele) => {
      expect(ele.first()).to.contain("Liste der Slots");
    });

    cy.contains('button', '+ Slot erstellen')
  });

  it('should be able to create, edit and delete a slot', () => {
    cy.visit('/time-slots');

    // Create Slot
    cy.get('button').contains('+ Slot erstellen').click();
    cy.contains('h3', 'Neuen Slot erstellen');
    cy.get('#Titel').clear().type('TestSlot1234');
    cy.get('#Limit').clear().type('14');
    cy.get('#Sortierung').clear().type('0');
    cy.contains('input', 'Speichern').click();

    // Verify that the slot was created
    cy.get('button').contains('+ Slot erstellen').click();
    cy.get('span').contains('TestSlot1234');
    cy.get('span').contains('14');

    // Edit Slot
    cy.get('span')
      .contains('TestSlot1234')
      .parent()
      .parent()
      .contains('p', 'Editieren')
      .click();
    cy.get('#Titel').clear().type('TestSlot5678');
    cy.get('#Limit').clear().type('17');
    cy.contains('input', 'Speichern').click();

    // Verify that the slot was successfully edited
    cy.get('span')
      .contains('TestSlot5678')
      .parent()
      .parent()
      .contains('p', 'Editieren')
      .click();
    cy.get('span').contains('17');

    // Disable Slot
    cy.get('span')
      .contains('TestSlot5678')
      .parent()
      .parent()
      .contains('span', 'Enable TimeSlot')
      .parent()
      .click();

    // Verify disabled Slot
    cy.get('span')
      .contains('TestSlot5678')
      .parent()
      .parent()
      .contains('span', 'Enable TimeSlot')
      .parent()
      .should('have.attr', 'aria-checked', 'false');

    // Enable Slot
    cy.get('span')
      .contains('TestSlot5678')
      .parent()
      .parent()
      .contains('span', 'Enable TimeSlot')
      .parent()
      .click();

    // Verify enabled Slot
    cy.get('span')
      .contains('TestSlot5678')
      .parent()
      .parent()
      .contains('span', 'Enable TimeSlot')
      .parent()
      .should('have.attr', 'aria-checked', 'true');

    // Delete Slot
    cy.get('span')
      .contains('TestSlot5678')
      .parent()
      .parent()
      .contains('p', 'Löschen')
      .click();

    // Verify that the slot was successfully deleted
    cy.get('span').contains('TestSlot5678').should('not.exist');
  });
});
