import { faker } from "@faker-js/faker";

describe('Register Test', () => {
  it('render the register page', () => {
    cy.visit('http://localhost:3000/register');
  });

  it.skip('show error when email and password input elements empty', () => {
    cy.get('button.btn-primary').click();
  });

  it('can type in the textbox', () => {
    cy.get('#name').type(faker.name.firstName() + faker.name.lastName(), { delay: 100 });
    cy.get('#email').type(faker.internet.email(), { delay: 100 });
    cy.get('#password').type('password');
    cy.get('#password_confirmation').type('password');    
  });

  it('throw error message in term and condition checkbox', () => {
    cy.get('button.btn-primary').click();
    
    cy.get('span.text-danger').should('be.visible');
  });
});