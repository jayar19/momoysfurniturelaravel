<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" sizes="32x32" href="/images/logo.png">
<link rel="apple-touch-icon" href="/images/logo.png">
  <title>Check Admin Status</title>
  <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
  <div class="container">
    <div class="form-container">
      <h2>Admin Status Check</h2>
      
      <div id="status-info" style="margin: 2rem 0;">
        <div class="spinner"></div>
      </div>
      
      <div style="text-align: center; margin-top: 2rem;">
        <button id="go-admin" class="btn btn-primary" style="display: none; margin-right: 1rem;">
          Go to Admin Panel
        </button>
        <button id="go-products" class="btn btn-secondary" style="display: none;">
          Go to Products
        </button>
        <a href="/login" class="btn btn-secondary" id="go-login" style="display: none;">
          Login
        </a>
      </div>
    </div>
  </div>
<script src="/js/config.js"></script>
  <script src="/js/firebase-config.js"></script>
  <script src="/js/auth.js"></script>
  <script>
    auth.onAuthStateChanged(async (user) => {
      const statusDiv = document.getElementById('status-info');
      
      if (!user) {
        statusDiv.innerHTML = `
          <div class="alert alert-error">
            <p>❌ Not logged in</p>
          </div>
        `;
        document.getElementById('go-login').style.display = 'inline-block';
        return;
      }
      
      try {
        const userDoc = await db.collection('users').doc(user.uid).get();
        
        if (!userDoc.exists) {
          statusDiv.innerHTML = `
            <div class="alert alert-error">
              <p>❌ User account was not found in PostgreSQL</p>
              <p style="margin-top: 1rem;">User ID: ${user.uid}</p>
              <p>Email: ${user.email}</p>
            </div>
          `;
          return;
        }
        
        const userData = userDoc.data();
        const isAdminUser = userData.role === 'admin';
        
        statusDiv.innerHTML = `
          <div class="alert ${isAdminUser ? 'alert-success' : 'alert-error'}">
            <h3>${isAdminUser ? '✅ Admin Account' : '👤 Regular User Account'}</h3>
            <p style="margin-top: 1rem;"><strong>Email:</strong> ${user.email}</p>
            <p><strong>User ID:</strong> ${user.uid}</p>
            <p><strong>Role:</strong> ${userData.role}</p>
            <p><strong>Full Name:</strong> ${userData.fullName}</p>
          </div>
          
          ${!isAdminUser ? `
            <div class="alert alert-error" style="margin-top: 1rem;">
              <h4>To make this user an admin:</h4>
              <ol style="text-align: left; margin: 1rem 0;">
                <li>Open the PostgreSQL database</li>
                <li>Find the row where collection_path is <code>users</code> and id is <code>${user.uid}</code></li>
                <li>Edit the JSON <code>role</code> value to <code>admin</code></li>
                <li>Save and refresh this page</li>
              </ol>
            </div>
          ` : ''}
        `;
        
        if (isAdminUser) {
          document.getElementById('go-admin').style.display = 'inline-block';
          document.getElementById('go-admin').onclick = () => {
            window.location.href = '/admin/dashboard';
          };
        } else {
          document.getElementById('go-products').style.display = 'inline-block';
          document.getElementById('go-products').onclick = () => {
            window.location.href = '/products';
          };
        }
        
      } catch (error) {
        statusDiv.innerHTML = `
          <div class="alert alert-error">
            <p>❌ Error checking admin status</p>
            <p style="margin-top: 1rem;">${error.message}</p>
          </div>
        `;
        console.error('Error:', error);
      }
    });
  </script>
</body>
</html>