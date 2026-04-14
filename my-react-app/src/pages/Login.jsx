// Login.jsx
function LoginPage({onLogin, onGoRegister}) {
  return (
    <div className="auth-page">
        {/*ROUGH NAVBAR TO UPDATE IF NEEDED*/}
            <nav className="navbar">
                <span className='nav-logo'>Meet Me @ McGill</span>
                <div className="nav-links">
                    <button onClick={onLogin}>Login</button>
                    <button onClick={onRegister} className="btn-primary">Register</button>
                </div>
            </nav>
      <div className="auth-card">
        <h2>Welcome back</h2>
        <p className="auth-subtitle">Login with your McGill email</p>

        <div className="auth-form">
          <div className="form-group">
            <label>Email</label>
            <input type="email" placeholder="you@mail.mcgill.ca" />
          </div>

          <div className="form-group">
            <label>Password</label>
            <input type="password" placeholder="Password" />
          </div>

          <button className="btn-primary btn-full">Login</button>
        </div>

        <p className="auth-switch">
          Don't have an account?{" "}
          <span onClick={onGoRegister} className="auth-link">Register here</span>
        </p>
      </div>

    </div>
  )
}
export default LoginPage