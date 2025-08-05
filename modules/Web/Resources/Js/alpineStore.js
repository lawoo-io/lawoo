
// Store

document.addEventListener('alpine:init', () => {
    Alpine.store('ui', {
        sidebarCollapsed: Alpine.$persist(false).as('sidebar_state'),

        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        },
    });
});
