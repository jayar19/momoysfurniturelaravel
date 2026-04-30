async function registerUser(email, password, fullName) {
  try {
    const originalFullName = document.getElementById('fullName')?.value;
    const tempInput = document.getElementById('reg-fullName') || document.getElementById('fullName');
    if (tempInput && fullName) tempInput.value = fullName;
    const userCredential = await auth.createUserWithEmailAndPassword(email, password);
    if (tempInput && originalFullName !== undefined) tempInput.value = originalFullName;
    return { success: true, user: userCredential.user };
  } catch (error) {
    return { success: false, error: friendlyAuthError(error.code) };
  }
}

async function loginUser(email, password) {
  try {
    const userCredential = await auth.signInWithEmailAndPassword(email, password);
    return { success: true, user: userCredential.user };
  } catch (error) {
    return { success: false, error: friendlyAuthError(error.code) };
  }
}

async function logoutUser() {
  try {
    await auth.signOut();
    window.location.href = '/';
    return { success: true };
  } catch (error) {
    return { success: false, error: error.message };
  }
}

async function isAdmin() {
  const user = auth.currentUser;
  if (!user) return false;

  try {
    const userDoc = await db.collection('users').doc(user.uid).get();
    return userDoc.exists && userDoc.data().role === 'admin';
  } catch (error) {
    console.error('Error checking admin status:', error);
    return false;
  }
}

async function protectAdminPage() {
  const user = auth.currentUser;
  if (!user) {
    window.location.href = '/login';
    return;
  }

  const admin = await isAdmin();
  if (!admin) {
    alert('Access denied. Admin only.');
    window.location.href = '/';
  }
}

function friendlyAuthError(code) {
  const map = {
    'auth/user-not-found': 'No account found with that email.',
    'auth/wrong-password': 'Incorrect password. Please try again.',
    'auth/invalid-email': 'Please enter a valid email address.',
    'auth/email-already-in-use': 'An account with this email already exists.',
    'auth/weak-password': 'Password must be at least 6 characters.',
    'auth/too-many-requests': 'Too many attempts. Please try again later.',
    'auth/invalid-credential': 'Incorrect email or password. Please try again.',
    'auth/invalid-login-credentials': 'Incorrect email or password. Please try again.',
    'auth/network-request-failed': 'Network error. Please check your connection.',
    'auth/user-disabled': 'This account has been disabled.'
  };
  return map[code] || 'Something went wrong. Please try again.';
}

function showFormMessage(msg, type) {
  const authMsg = document.getElementById('auth-message');
  if (authMsg) {
    authMsg.textContent = msg;
    authMsg.className = `auth-message ${type === 'success' ? 'success' : 'error'}`;
    authMsg.style.display = 'block';
    return;
  }

  const messageDiv = document.getElementById('message');
  if (messageDiv) {
    messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'error'}`;
    messageDiv.textContent = msg;
    messageDiv.style.display = 'block';
  }
}

const logoutBtn = document.getElementById('logout-btn');
if (logoutBtn) {
  logoutBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    await logoutUser();
  });
}

auth.onAuthStateChanged((user) => {
  const authRequired = document.querySelectorAll('.auth-required');
  const guestOnly = document.querySelectorAll('.guest-only');

  if (user) {
    authRequired.forEach((el) => {
      el.classList.add('visible');
      el.classList.remove('hidden');
    });
    guestOnly.forEach((el) => {
      el.classList.add('hidden');
      el.classList.remove('visible');
    });
  } else {
    authRequired.forEach((el) => {
      el.classList.remove('visible');
      el.classList.add('hidden');
    });
    guestOnly.forEach((el) => {
      el.classList.remove('hidden');
      el.classList.add('visible');
    });
  }

  if (typeof updateCartCount === 'function') updateCartCount();
});
