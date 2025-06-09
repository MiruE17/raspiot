function data() {
  // Pastikan dark mode selalu aktif
  if (document && document.documentElement) {
    document.documentElement.classList.add('theme-dark');
  }

  // Trigger event untuk memperbarui chart jika ada
  window.dispatchEvent(new CustomEvent('theme-change'));

  return {
    // Selalu aktifkan dark mode
    dark: true,
    
    // Fungsi toggle tidak mengubah mode, tetapi tetap kita pertahankan
    // untuk mencegah error jika dipanggil di template
    toggleTheme() {
      // Tidak melakukan apa-apa - dark mode tetap aktif
    },
    
    isSideMenuOpen: false,
    toggleSideMenu() {
      this.isSideMenuOpen = !this.isSideMenuOpen
    },
    closeSideMenu() {
      this.isSideMenuOpen = false
    },
    
    isProfileMenuOpen: false,
    toggleProfileMenu() {
      this.isProfileMenuOpen = !this.isProfileMenuOpen
    },
    closeProfileMenu() {
      this.isProfileMenuOpen = false
    },
    
    isActive(route) {
      return window.location.href.indexOf(route) > -1;
    }
  }
}
