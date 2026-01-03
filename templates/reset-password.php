<div style="max-width: 400px; margin: 4rem auto;">
  <div class="card glass">
    <h1 style="margin-bottom: 0.5rem; font-size: 1.5rem; font-weight: 600;">Reset Password</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 0.875rem;">Enter your new password below</p>

    <form id="resetForm">
      <input type="hidden" id="token" value="<?= $_GET['token'] ?? '' ?>">
      <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" class="form-control" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Update Password</button>
    </form>
  </div>
</div>

<script>
  document.getElementById('resetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const token = document.getElementById('token').value;
    const password = document.getElementById('password').value;

    if (!token) {
      alert('Token is missing');
      return;
    }

    const response = await fetch('/api/auth/reset-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, password })
    });

    if (response.ok) {
      alert('Password reset successfully! You can now log in.');
      window.location.href = '/login';
    } else {
      const data = await response.json();
      alert(data.error || 'Reset failed');
    }
  });
</script>