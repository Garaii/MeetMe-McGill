// Login.jsx
import { useState } from 'react'
import Navbar from '../components/Navbar'
import { apiPost } from '../api'

function LoginPage({onLogin, onGoRegister}) {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [message, setMessage] = useState("")
  const [isLoading, setIsLoading] = useState(false)

  async function handleLoginClick() {
    setMessage("")
    setIsLoading(true)

    try {
      const result = await apiPost("login.php", {
        email: email,
        password: password,
      })

      onLogin(result.user)
    } catch (error) {
      setMessage(error.message)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div>
      <Navbar onLogin={null} onRegister={onGoRegister} />

      <div className="auth-page">
        <div className="auth-card">
          <h2>Welcome back</h2>
          <p className="auth-subtitle">Login with your McGill email</p>

          <div className="auth-form">
            <div className="form-group">
              <label>Email</label>
              <input
                type="email"
                placeholder="you@mail.mcgill.ca"
                value={email}
                onChange={(event) => setEmail(event.target.value)}
              />
            </div>

            <div className="form-group">
              <label>Password</label>
              <input
                type="password"
                placeholder="Password"
                value={password}
                onChange={(event) => setPassword(event.target.value)}
              />
            </div>

            {message !== "" && <p className="form-message">{message}</p>}

            <button
              className="btn-primary btn-full"
              onClick={handleLoginClick}
              disabled={isLoading}
            >
              {isLoading ? "Logging in..." : "Login"}
            </button>
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