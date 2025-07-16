describe('Student Payment Update and Finalization', () => {
  beforeEach(() => {
    cy.session('cashier-login', () => {
      cy.visit('http://127.0.0.1:8000/login')
      cy.get('input[name="email"]').type('jamesudesu0818@gmail.com')
      cy.get('input[name="password"]').type('password')
      cy.get('form').submit()
      cy.url().should('include', '/admin/dashboard')
    })

    cy.visit('http://127.0.0.1:8000/admin/payments/pending')
    cy.contains('Pending Payments', { timeout: 5000 }).should('exist')
  })

  it('Updates a pending payment and finalizes with a receipt', () => {
    // Step 1: Click the edit button of the first transaction
    cy.get('table tbody tr').first().within(() => {
      cy.get('a[title="View and Edit Payment"]').click()
    })

    // Step 2: Wait for payment form to load
    cy.contains('Student Payment Form', { timeout: 5000 }).should('exist')
    cy.url().should('include', '/payments/pending') // Modify if your edit URL is different

    // Step 3: Update the first quantity input
    cy.get('.fee-quantity').first().clear().type('2')
    cy.wait(300) // brief pause for any oninput JS logic
    cy.get('button#confirmPaymentButton').click()

    // Step 4: Wait for redirect and check confirmation
    cy.url({ timeout: 10000 }).should('include', '/transactions/customer/') // Adjust this route
    cy.contains('Student Transaction Details', { timeout: 5000 }).should('exist')
    cy.contains('Total Amount:').should('exist')

    // Step 5: Trigger the receipt modal
    cy.get('#viewPrintReceiptBtn').click()
    cy.wait(300) // Wait for modal animation

    // Step 6: Fill in receipt number
    cy.get('#modal_receipt_number').should('be.visible').type('Testing000002')
    cy.get('#confirmPaymentButton').click()
    cy.wait(15000) // Wait for modal animation

    // Step 7: Wait for page reload and confirmation
    cy.contains('Testing000002').should('exist') // Receipt number appears
  })
})
