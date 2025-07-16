describe('Logout', () => {
  it('Logs the user out and redirects to login', () => {
    // Login first
    cy.visit('http://127.0.0.1:8000/login')
    cy.get('input[name="email"]').type('jamesudesu0818@gmail.com')
    cy.get('input[name="password"]').type('password')
    cy.get('form').submit()
    
    // Step 2: Expand the collapsed sidebar
    cy.get('#toggleSidebar').click()

    // Step 3: Expand "Account" submenu
    cy.contains('Account').click()

    // Step 4: Click "Sign out"
    cy.contains('Sign out').click()

    // Step 5: Verify redirection to login page
    cy.url().should('include', '/login')
    cy.contains('Login').should('exist')
  })
})