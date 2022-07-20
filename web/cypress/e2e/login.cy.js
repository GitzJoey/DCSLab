describe('Login', () => {
  it('render the login page', () => {
    cy.visit('http://localhost:3000/')
  });

  it('focus on email input element', () => {
    cy.get('#email').focus();
  });

  it('show error when email and password input elements empty', () => {
    cy.get('.btn-primary').click();
  });

  it('can login', () => {
    cy.get('#email').type('gitzjoey@yahoo.com');
    cy.get('#password').type('qweasdzxc');
    cy.get('.btn-primary').click();
  })
})