/**
 * Authentication Helper
 * Manages auth tokens and API requests across the application
 */

class AuthManager {
  constructor() {
    this.tokenKey = 'auth_token';
    this.userKey = 'user';
    this.apiBase = '/api';
  }

  /**
   * Get the stored auth token
   */
  getToken() {
    return localStorage.getItem(this.tokenKey);
  }

  /**
   * Get the stored user data
   */
  getUser() {
    const userData = localStorage.getItem(this.userKey);
    return userData ? JSON.parse(userData) : null;
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return !!this.getToken();
  }

  /**
   * Check if user is an admin
   */
  isAdmin() {
    const user = this.getUser();
    return user && user.role === 'admin';
  }

  /**
   * Make authenticated API request
   */
  async request(endpoint, options = {}) {
    const token = this.getToken();
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers
    };

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${this.apiBase}${endpoint}`, {
      ...options,
      headers
    });

    // If unauthorized, clear auth and redirect to login
    if (response.status === 401) {
      this.logout();
      window.location.href = '/login';
      return null;
    }

    return response;
  }

  /**
   * Store auth token and user data
   */
  setAuth(token, user) {
    localStorage.setItem(this.tokenKey, token);
    localStorage.setItem(this.userKey, JSON.stringify(user));
  }

  /**
   * Clear auth data
   */
  logout() {
    localStorage.removeItem(this.tokenKey);
    localStorage.removeItem(this.userKey);
  }

  /**
   * Redirect to login if not authenticated
   */
  requireAuth() {
    if (!this.isAuthenticated()) {
      window.location.href = '/login';
      return false;
    }
    return true;
  }

  /**
   * Redirect to login if not admin
   */
  requireAdmin() {
    if (!this.isAdmin()) {
      window.location.href = '/login';
      return false;
    }
    return true;
  }
}

// Create global instance
const auth = new AuthManager();

// Auto-logout if token is missing but we're on a protected page
document.addEventListener('DOMContentLoaded', () => {
  // Restore user data from token if needed
  if (auth.isAuthenticated() && !auth.getUser()) {
    auth.logout();
  }
});
