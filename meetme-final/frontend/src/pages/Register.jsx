// Register.jsx
import { useState } from 'react'
import Navbar from '../components/Navbar'
import { apiPost } from '../api'

function RegisterPage({onRegistered, onGoLogin}) {
  const [firstName, setFirstName] = useState("")
  const [lastName, setLastName] = useState("")
  const [mcgillId, setMcgillId] = useState("")
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [confirmPassword, setConfirmPassword] = useState("")
  const [message, setMessage] = useState("")
  const [isLoading, setIsLoading] = useState(false)

  async function handleRegisterClick() {
    setMessage("")

    if (
      firstName.trim() === "" ||
      lastName.trim() === "" ||
      email.trim() === "" ||
      password === "" ||
      confirmPassword === ""
    ) {
      setMessage("Please fill in all required fields.")
      return
    }

    if (password !== confirmPassword) {
      setMessage("The two passwords do not match.")
      return
    }

    setIsLoading(true)

    try {
      const result = await apiPost("register.php", {
        first_name: firstName,
        last_name: lastName,
        mcgill_id: mcgillId,
        email: email,
        password: password,
        confirm_password: confirmPassword,
      })

      onRegistered(result.user)
    } catch (error) {
      setMessage(error.message)
    } finally {
      setIsLoading(false)
    }
  }

  return (   
    <div>
      <Navbar onLogin={onGoLogin} onRegister={null} />

      <div className="auth-page">
        <div className="auth-card">
          <h2>Create an account</h2>
          <p className="auth-subtitle">Only McGill emails are allowed</p>

          <div className="auth-form">
            <div className='form-row'>
              <div className="form-group">
                <label>First Name</label>
                <input
                  type="text"
                  placeholder="First Name"
                  value={firstName}
                  onChange={(event) => setFirstName(event.target.value)}
                />
              </div>

              <div className="form-group">
                <label>Last Name</label>
                <input
                  type="text"
                  placeholder="Last Name"
                  value={lastName}
                  onChange={(event) => setLastName(event.target.value)}
                />
              </div>
            </div>

            <div className="form-group">
              <label>McGill ID</label>
              <input
                type="text"
                placeholder="270000000"
                value={mcgillId}
                onChange={(event) => setMcgillId(event.target.value)}
              />
            </div>

            <div className="form-group">
              <label>McGill Email</label>
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

            <div className="form-group">
              <label>Confirm Password</label>
              <input
                type="password"
                placeholder="Confirm password"
                value={confirmPassword}
                onChange={(event) => setConfirmPassword(event.target.value)}
              />
            </div>

            {message !== "" && <p className="form-message">{message}</p>}

            <button
              className="btn-primary btn-full"
              onClick={handleRegisterClick}
              disabled={isLoading}
            >
              {isLoading ? "Creating account..." : "Register"}
            </button>
          </div>

          <p className="auth-switch">
            Already have an account?{" "}
            <span onClick={onGoLogin} className="auth-link">Login here</span>
          </p>
        </div>
      </div>
    </div>
  )
}

export default RegisterPage