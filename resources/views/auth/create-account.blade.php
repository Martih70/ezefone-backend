<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Your Account — Ezefone</title>
  <link rel="icon" href="/favicon.ico" sizes="any">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(160deg, #1a6b3c 0%, #0f3d22 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .card {
      background: #fff;
      border-radius: 24px;
      padding: 36px 28px 32px;
      max-width: 420px;
      width: 100%;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .logo {
      display: block;
      width: 64px;
      height: 64px;
      border-radius: 14px;
      margin: 0 auto 16px;
    }
    h1 {
      text-align: center;
      font-size: 1.6rem;
      font-weight: 800;
      color: #1a6b3c;
      margin-bottom: 6px;
    }
    .subtitle {
      text-align: center;
      color: #666;
      font-size: 0.95rem;
      margin-bottom: 28px;
    }
    .alert {
      background: #fef2f2;
      border: 1px solid #fca5a5;
      color: #b91c1c;
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 0.9rem;
      margin-bottom: 20px;
    }
    label {
      display: block;
      font-weight: 700;
      font-size: 0.95rem;
      color: #333;
      margin-bottom: 6px;
    }
    input {
      width: 100%;
      padding: 14px 16px;
      border: 2px solid #e2e8e4;
      border-radius: 12px;
      font-size: 1rem;
      margin-bottom: 18px;
      outline: none;
      transition: border-color 0.2s;
    }
    input:focus { border-color: #1a6b3c; }
    .error { color: #b91c1c; font-size: 0.85rem; margin-top: -14px; margin-bottom: 14px; }
    button {
      width: 100%;
      background: #1a6b3c;
      color: #fff;
      font-size: 1.1rem;
      font-weight: 800;
      padding: 16px;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      margin-top: 4px;
    }
    button:active { background: #0f3d22; }
    .login-link {
      text-align: center;
      margin-top: 16px;
      font-size: 0.9rem;
      color: #666;
    }
    .login-link a { color: #1a6b3c; font-weight: 700; text-decoration: none; }
  </style>
</head>
<body>
  <div class="card">
    <img src="/icons/favicon-48x48.png" alt="Ezefone" class="logo">
    <h1>Create Your Account</h1>
    <p class="subtitle">One last step — set up your account to access Ezefone</p>

    @if ($errors->any())
      <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/create-account">
      @csrf

      <label for="name">Your Name</label>
      <input id="name" name="name" type="text" value="{{ old('name') }}"
             placeholder="e.g. Margaret" required autofocus>
      @error('name') <p class="error">{{ $message }}</p> @enderror

      <label for="email">Email Address</label>
      <input id="email" name="email" type="email"
             value="{{ old('email', $email) }}"
             placeholder="your@email.com" required>
      @error('email') <p class="error">{{ $message }}</p> @enderror

      <label for="password">Choose a Password</label>
      <input id="password" name="password" type="password"
             placeholder="At least 8 characters" required>
      @error('password') <p class="error">{{ $message }}</p> @enderror

      <label for="password_confirmation">Confirm Password</label>
      <input id="password_confirmation" name="password_confirmation"
             type="password" placeholder="Repeat your password" required>

      <button type="submit">Create Account &amp; Open Ezefone</button>
    </form>

    <p class="login-link">
      Already have an account? <a href="/login">Sign in</a>
    </p>
  </div>
</body>
</html>
