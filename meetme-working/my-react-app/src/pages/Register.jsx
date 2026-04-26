// Register.jsx
import Navbar from '../components/Navbar'
function RegisterPage({onRegistered, onGoLogin}) {
  return (   
 <div><Navbar onLogin={onGoLogin} onRegister={null} />
  <div className="auth-page">
        

      <div className="auth-card">
        <h2>Create an account</h2>
        <p className="auth-subtitle">Only McGill emails are allowed</p>

        <div className="auth-form">

          <div className='form-row'>
            <div className="form-group">
              <label>First Name</label>
              <input type="fname" placeholder="First Name" />
            </div>

            <div className="form-group">
              <label>Last Name</label>
              <input type="lname" placeholder="Last Name" />
            </div>
          </div>

          <div className="form-group">
            <label>McGill ID</label>
            <input type="numID" placeholder="270000000"/>
          </div>

          <div className="form-group">
            <label>McGill Email</label>
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
           {/* TO REPLACE WITH REAL CALL TO PHP FETCH */}
          <button className="btn-primary btn-full" onClick={()=> onRegistered({ 
            name: "Kevin", 
            email: "tame.impala@mail.mcgill.ca", 
            role: "student" 
          })}>
            Register
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