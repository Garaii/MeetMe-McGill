// Register.jsx
function RegisterPage() {
  return (    <div className="auth-page">

      <div className="auth-card">
        <h2>Create an account</h2>
        <p className="auth-subtitle">Only McGill emails are allowed</p>

        <div className="auth-form">
          <div className="form-group">
            <label>Email</label>
            <input type="email" placeholder="you@mail.mcgill.ca" />
          </div>

          <div className="form-group">
            <label>Password</label>
            <input type="password" placeholder="Password" />
          </div>

          <div className="form-group">
            <label>Confirm Password</label>
            <input type="password" placeholder="Confirm password" />
          </div>

          <button className="btn-primary btn-full">Register</button>
        </div>

        <p className="auth-switch">
          Already have an account?{" "}
          <span onClick={onGoLogin} className="auth-link">Login here</span>
        </p>
      </div>

    </div>)
}
export default RegisterPage