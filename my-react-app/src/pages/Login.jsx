// Login.jsx
import Navbar from '../components/Navbar'

function LoginPage({onLogin, onGoRegister}) {
  return (
    <div><Navbar onLogin={null} onRegister={onGoRegister} />
    <div className="auth-page">
      <div className='auth-box'>  
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
            
            {/* TO REPLACE WITH REAL CALL TO PHP FETCH */}
            <button className="btn-primary btn-full" onClick={()=> onLogin({ 
              name: "Kevin", 
              email: "tame.impala@mail.mcgill.ca", 
              role: "owner" 
            })}>
              Login
            </button>
          </div>

          <p className="auth-switch">
            Don't have an account?{" "}
            <span onClick={onGoRegister} className="auth-link">Register here</span>
          </p>
        </div>
      </div>
    </div>
    </div>
  )
}
export default LoginPage