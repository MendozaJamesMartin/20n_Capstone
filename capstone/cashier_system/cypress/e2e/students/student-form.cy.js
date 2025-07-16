describe('Student Payment Submission', () => {
  it('Submits the payment form and gets redirected to submitted page', () => {
    cy.visit('http://127.0.0.1:8000/')

    // Click the student button
    cy.contains('h4', 'Students').click()
    cy.url().should('include', '/student/payment')

    // Fill out student details
    cy.get('input[name="student_id"]').type('0000-00000-AA-0')
    cy.get('input[name="first_name"]').type('Juan')
    cy.get('input[name="middle_name"]').type('Santos')
    cy.get('input[name="last_name"]').type('Dela Cruz')
    cy.get('input[name="suffix"]').type('Jr.')
    cy.get('input[name="email"]').type('juan@example.com')

    // Add fee row and fill it
    cy.get('#fees-table tbody tr').should('exist')

    cy.get('#fees-table tbody tr').first().within(() => {
      cy.get('.fee-name').type('Certification/Authentication (per document)')
      cy.get('.fee-quantity').clear().type('10')
    })

    // Wait for auto-filled amount
    cy.get('.fee-amount').should('not.have.value', '')

    // ✅ Call the click programmatically to trigger your custom JS
    cy.window().then((win) => {
      win.document.getElementById('confirmPaymentButton').click();
    })

    // ✅ Wait for the redirected route
    cy.location('pathname', { timeout: 5000 }).should('match', /\/customer\/student\/payment\/submitted\/\d+$/)

    // Optionally confirm the page shows success
    cy.contains('Transaction Submitted').should('exist') // Replace with actual message
  })
})
