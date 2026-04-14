// Login.jsx
import Navbar from '../components/Navbar'

function LoginPage({onLogin, onGoRegister}) {
  return (
    <div><Navbar onLogin={onGoLogin} onRegister={null} />
    <div className="auth-page">
       
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
    </div>
  )
}
export default LoginPage