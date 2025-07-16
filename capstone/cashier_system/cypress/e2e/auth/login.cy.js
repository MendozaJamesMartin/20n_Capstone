describe('Laravel Login Page', () => {
  it('Allows a user to log in', () => {
    cy.visit('http://127.0.0.1:8000/login')

    cy.get('input[name="email"]').type('jamesudesu0818@gmail.com')
    cy.get('input[name="password"]').type('password')

    cy.get('form').submit()

    cy.url().should('include', '/admin/dashboard') // adjust to your post-login route
  })
})