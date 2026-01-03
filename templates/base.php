<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Storender' ?></title>
  <link rel="icon" type="image/png" href="/favicon.png">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- AlpineJS -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Tailwind + DaisyUI -->
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
        }
      }
    }
  </script>

  <style>
    [x-cloak] {
      display: none !important;
    }

    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="min-h-screen bg-base-300 transition-colors duration-300"
  x-data="{ theme: localStorage.getItem('theme') || 'dark' }" :data-theme="theme">
  <?php if (isset($showNav) && $showNav): ?>
    <div class="navbar bg-base-100 shadow-lg px-4 lg:px-8 border-b border-base-200 sticky top-0 z-50">
      <div class="flex-1">
        <a href="/"
          class="text-xl font-bold tracking-tight bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent italic">STORENDER</a>
      </div>
      <div class="flex-none gap-2 lg:gap-4">
        <!-- Theme Controller -->
        <label class="swap swap-rotate btn btn-ghost btn-circle btn-sm border border-base-200">
          <input type="checkbox"
            @change="theme = (theme === 'light' ? 'dark' : 'light'); localStorage.setItem('theme', theme)"
            :checked="theme === 'light'" />
          <!-- sun icon -->
          <svg class="swap-on fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
              d="M5.64,17l-.71,.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
          </svg>
          <!-- moon icon -->
          <svg class="swap-off fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
              d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
          </svg>
        </label>

        <ul class="menu menu-horizontal px-1">
          <li><a href="/" class="font-medium hidden sm:inline-flex">Dashboard</a></li>
        </ul>
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar border border-base-200">
            <div class="w-10 rounded-full flex items-center justify-center bg-base-200">
              <span class="text-xs font-bold"><?= strtoupper(substr($_SESSION['email'] ?? 'U', 0, 1)) ?></span>
            </div>
          </div>
          <ul tabindex="0"
            class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52 border border-base-200">
            <li class="px-3 py-2 text-xs opacity-50"><?= $_SESSION['email'] ?? 'User' ?></li>
            <div class="divider my-0"></div>
            <li><a href="/logout" class="text-error">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <main class="<?= isset($fullWidth) && $fullWidth ? '' : 'container mx-auto p-4 lg:p-8' ?>">
    <?= $content ?>
  </main>

  <!-- Global Notification System (Toast) -->
  <div x-data="{ 
        notifications: [],
        add(msg, type = 'info') {
            const id = Date.now();
            this.notifications.push({ id, msg, type });
            setTimeout(() => this.notifications = this.notifications.filter(n => n.id !== id), 3000);
        }
    }" @notify.window="add($event.detail.message, $event.detail.type)" class="toast toast-end">
    <template x-for="n in notifications" :key="n.id">
      <div :class="{
                'alert': true,
                'alert-info': n.type === 'info',
                'alert-success': n.type === 'success',
                'alert-error': n.type === 'error',
                'alert-warning': n.type === 'warning'
            }" class="shadow-lg">
        <span x-text="n.msg"></span>
      </div>
    </template>
  </div>
</body>

</html>