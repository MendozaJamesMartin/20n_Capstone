describe('Student Payment Update and Finalization', () => {
  beforeEach(() => {
    cy.session('cashier-login', () => {
      cy.visit('http://127.0.0.1:8000/login')
      cy.get('input[name="email"]').type('jamesudesu0818@gmail.com')
      cy.get('input[name="password"]').type('password')
      cy.get('form').submit()
      cy.url().should('include', '/admin/dashboard')
    })

    cy.visit('http://127.0.0.1:8000/admin/payments/outsider/new')
    cy.contains('Student Payment Form', { timeout: 5000 }).should('exist')
  })

  it('Creates a student payment and finalizes with a receipt', () => {
    const testReceipt = 'Testing000004';

    // Fill out student details
    cy.get('input[name="name"]').type('Cypress Test')
    cy.get('input[name="contact"]').type('contact@example.com')

    // Add fee row and fill it
    cy.get('#fees-table tbody tr').should('exist')

    cy.get('#fees-table tbody tr').first().within(() => {
      cy.get('.fee-name').type('Certification/Authentication (per document)')
      cy.get('.fee-quantity').clear().type('5')
    })

    // Wait for auto-filled amount
    cy.get('.fee-amount').should('not.have.value', '')

    // ✅ Call the click programmatically to trigger your custom JS
    cy.window().then((win) => {
      win.document.getElementById('confirmPaymentButton').click();
    })

    // Step 4: Wait for redirect and check confirmation
    cy.url({ timeout: 10000 }).should('include', '/transactions/customer/') // Adjust this route
    cy.contains('Student Transaction Details', { timeout: 5000 }).should('exist')
    cy.contains('Total Amount:').should('exist')

    // Step 5: Trigger the receipt modal
    cy.get('#viewPrintReceiptBtn').click()
    cy.wait(300) // Wait for modal animation

    // Step 6: Fill in receipt number
    cy.get('#modal_receipt_number').should('be.visible').type(testReceipt)
    cy.get('#confirmPaymentButton').click()
    cy.wait(15000) // Wait for modal animation

    // Step 7: Wait for page reload and confirmation
    cy.contains(testReceipt).should('exist') // Receipt number appears
  })
})
