describe('Testing Shop Accessibility', function () {
    it('Nav links have aria', () => {
        cy.visit("https://shopware.dev.die-etagen.de/")
        cy.get("nav.main-navigation-menu > a.nav-link")
            .should('have.attr', 'aria-label')
    })
    it('Nav container has role', () => {
        cy.visit("https://shopware.dev.die-etagen.de/")
        cy.get("nav.main-navigation-menu")
            .should('have.attr', 'role').and('include', 'navigation')
    })
});
