describe('Full Concessionaire Billing and Payment Flow', () => {
  before(() => {
    // LOGIN (adjust credentials accordingly)
    cy.visit('http://127.0.0.1:8000/login');
    cy.get('input[name="email"]').type('jamesudesu0818@gmail.com'); // use valid admin email
    cy.get('input[name="password"]').type('password');    // use valid password
    cy.get('button[type="submit"]').click();

    // Assert login success (redirect to dashboard or specific element)
    cy.url().should('not.include', '/login');
    cy.contains('Welcome').should('exist'); // change if needed
  });

  it('creates a new bill and completes full payment with receipt', () => {
    const testAmount = '500';
    const testReceipt = 'Testing000005'; // unique receipt
    const today = new Date().toISOString().split('T')[0];

    // STEP 1: Create a new concessionaire bill
    cy.visit('http://127.0.0.1:8000/admin/concessionaires/billing/new');

    cy.get('select[name="concessionaire_id"]')
      .should('exist')
      .select(5); // Replace with valid ID or use .contains('Concessionaire Name')

    cy.get('select[name="utility_type"]').select('Water');

    cy.get('input[name="bill_amount"]').type(testAmount);
    cy.get('input[name="due_date"]').type(today);

    cy.get('button[type="submit"]').click();

    // Confirm success
    cy.contains('successfully').should('exist');

    // STEP 2: Pay the bill
    cy.visit('http://127.0.0.1:8000/admin/concessionaires/billing/payment');

    cy.get('select[name="concessionaire_id"]').select(6); // same ID used above

    cy.wait(1000); // Wait for bill load

    // Select the first unpaid bill
    cy.get('input[type="checkbox"][name="bill_id[]"]').first().check();

    // Input full payment
    cy.get('input[name^="amount["]').first().invoke('attr', 'max').then((maxValue) => {
      cy.get('input[name^="amount["]').first().type(maxValue);
    });

    // Submit payment
    cy.get('button#confirmPaymentButton').click();

    // Assert transaction detail page loads
    cy.contains('Concessionaire Transaction Details').should('exist');

    // STEP 3: Enter receipt number and confirm
    cy.get('#viewPrintReceiptBtn').click()
    cy.wait(300) // Wait for modal animation

    // Step 6: Fill in receipt number
    cy.get('#modal_receipt_number').should('be.visible').type(testReceipt)
    cy.get('button#confirmPaymentButton').click();
    cy.wait(15000) // Wait for modal animation

    // Step 7: Wait for page reload and confirmation
    cy.contains(testReceipt).should('exist') // Receipt number appears
  });
});
