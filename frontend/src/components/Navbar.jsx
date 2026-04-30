/* CONTRIBUTORS AND TASK COORDINATION
IKRAM 

IKRAM: CREATED THE NAVBAR UI THAT CORRESPOND TO EVRY PAGE IT IS CALLED ON;
      - REGISTER BUTTON ON LANDING AND LOGIN PAGE
      - LOGIN BUTTON ON LANDING AND REGISTER PAGE
      - CONTAINS THE ACTIONS OF THE DIFFERENT TYPES OF USERS DEPENDING ON THEIR ROLES
        AS AN EASIER FLOW AND UI SO THAT IT CAN BE MORE AESTHETIC 
 
*/ 
// Navbar.jsx
function Navbar({ user, onLogin, onRegister, onLogout }) {
  return (
    <nav className="navbar">
     
      <div className="nav-logo">
        <svg
          width="180"
          height="66"
          viewBox="0 0 600 220"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          {/* Code bracket symbol */}
          <g transform="translate(30, 110)">
            <path
              d="M 20 -40 L 0 -40 L 0 40 L 20 40 M 60 -40 L 80 -40 L 80 40 L 60 40"
              stroke="#ed1b2f"
              strokeWidth="6"
              strokeLinecap="round"
              fill="none"
            />
            <circle cx="40" cy="0" r="24" fill="#ed1b2f" />
            <text
              x="40"
              y="10"
              fontFamily="system-ui, -apple-system, sans-serif"
              fontSize="32"
              fontWeight="600"
              fill="white"
              textAnchor="middle"
            >
              @
            </text>
          </g>
          {/* Text */}
          <text
            x="150"
            y="100"
            fontFamily="'Courier New', monospace"
            fontSize="44"
            fontWeight="700"
            fill="#ed1b2f"
            letterSpacing="-1"
          >
            MEET ME
          </text>
          <text
            x="150"
            y="140"
            fontFamily="system-ui, -apple-system, sans-serif"
            fontSize="28"
            fontWeight="300"
            fill="#ed1b2f"
            letterSpacing="4"
          >
            @ MCGILL
          </text>
        </svg>
      </div>

      <div className="nav-links">
        {user && <span className="nav-user">{user.name}</span>}

        {onLogin && <button onClick={onLogin}>Login</button>}

        {onRegister && (
          <button onClick={onRegister} className="btn-primary">
            Register
          </button>
        )}

        {onLogout && <button onClick={onLogout}>Logout</button>}
      </div>
    </nav>
  )
}

export default Navbar