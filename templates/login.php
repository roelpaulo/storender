<div class="flex items-center justify-center min-h-[80vh] relative">
  <div class="absolute top-4 right-4" x-data="{ theme: localStorage.getItem('theme') || 'dark' }"
    x-init="$watch('theme', v => { document.body.setAttribute('data-theme', v); localStorage.setItem('theme', v) })">
    <label
      class="swap swap-rotate btn btn-ghost btn-circle border border-base-200 shadow-sm transition-transform hover:scale-105">
      <input type="checkbox" @change="theme = (theme === 'light' ? 'dark' : 'light')" :checked="theme === 'light'" />
      <svg class="swap-on fill-current w-5 h-5 transition-all duration-300" xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24">
        <path
          d="M5.64,17l-.71,.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
      </svg>
      <svg class="swap-off fill-current w-5 h-5 transition-all duration-300" xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24">
        <path
          d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
      </svg>
    </label>
  </div>
  <div class="card w-full max-w-md bg-base-100 shadow-2xl border border-base-200" x-data="loginForm()">
    <div class="card-body gap-6">
      <div class="text-center">
        <h2 class="card-title text-2xl font-bold block">Welcome Back</h2>
        <p class="text-base-content/60 text-sm mt-1">Sign in to manage your files</p>
      </div>

      <form @submit.prevent="submit">
        <div class="form-control w-full">
          <label class="label">
            <span class="label-text">Email Address</span>
          </label>
          <input type="email" x-model="email" placeholder="you@example.com" class="input input-bordered w-full"
            required />
        </div>

        <div class="form-control w-full mt-2">
          <label class="label">
            <span class="label-text">Password</span>
            <a href="#" @click.prevent="forgotPassword" class="label-text-alt link link-hover text-primary">Forgot?</a>
          </label>
          <input type="password" x-model="password" placeholder="••••••••" class="input input-bordered w-full"
            required />
        </div>

        <div class="card-actions mt-6">
          <button type="submit" class="btn btn-primary w-full" :class="loading && 'loading'" :disabled="loading">
            <span x-show="!loading">Sign In</span>
          </button>
        </div>
      </form>

      <div class="text-center text-sm">
        <span class="text-base-content/60">Don't have an account?</span>
        <a href="/register" class="link link-primary font-medium">Create Account</a>
      </div>
    </div>
  </div>
</div>

<script>
  function loginForm() {
    return {
      email: '',
      password: '',
      loading: false,
      init() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('verified')) {
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Email verified successfully! You can now sign in.', type: 'success' } }));
        }
        if (params.has('error')) {
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: params.get('error'), type: 'error' } }));
        }
      },
      async submit() {
        this.loading = true;
        try {
          const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: this.email, password: this.password })
          });

          const data = await response.json();
          if (response.ok) {
            window.location.href = '/';
          } else {
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.error || 'Login failed', type: 'error' } }));
          }
        } catch (err) {
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Network error', type: 'error' } }));
        } finally {
          this.loading = false;
        }
      },
      async forgotPassword() {
        if (!this.email) {
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Enter your email first', type: 'warning' } }));
          return;
        }
        // Implementation...
        const response = await fetch('/api/auth/forgot-password', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email: this.email })
        });
        const data = await response.json();
        window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: response.ok ? 'success' : 'error' } }));
      }
    }
  }
</script>