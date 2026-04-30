/*
CONTRIBUTORS AND TASK COORDINATION
IKRAM 

IKRAM: basic app routing for the different pages

*/
import { useState, useEffect } from "react"
import './App.css'

import LandingPage from './pages/Landing'
import LoginPage from './pages/Login'
import DashboardPage from './pages/Dashboard'
import RegisterPage from './pages/Register'


import OwnerDashboard from './pages/owner/OwnerDashboard'


import { apiGet, apiPost } from './api'

function App() {
  const [user, setUser] = useState(null)
  const [page, setPage] = useState("landing")
  const [checkingSession, setCheckingSession] = useState(true)

  // check if PHP already has a logged-in session
  useEffect(() => {
    async function checkSession() {
      try {
        const result = await apiGet("auth.php")

        if (result.logged_in && result.user) {
          setUser(result.user)

          const params = new URLSearchParams(window.location.search)
          const ownerId = params.get("owner_id")

          if (ownerId) {
            setPage("dashboard")
          } else if (result.user.role === "owner") {
            setPage("ownerDashboard")
          } else {
            setPage("dashboard")
          }
        }
      } catch (error) {
        setUser(null)
        setPage("landing")
      } finally {
        setCheckingSession(false)
      }
    }

    checkSession()
  }, [])

  // first just handling if the user is an owner or student
  function handleLogin(userData) {
    setUser(userData)

    const params = new URLSearchParams(window.location.search)
    const ownerId = params.get("owner_id")

    // redirect based on role after login
    if (ownerId) {
      setPage("dashboard")
    } else if (userData.role === "owner") {
      setPage("ownerDashboard")
    } else {
      setPage("dashboard")
    }
  }

  async function handleLogout() {
    try {
      await apiPost("logout.php")
    } catch (error) {
      // still clear the frontend even if the request fails
    }

    setUser(null)
    setPage("landing")
  }

  if (checkingSession) {
    return (
      <div className="app_shell">
        <div className="auth-page">
          <div className="auth-card">
            <p>Loading MeetMe@McGill...</p>
          </div>
        </div>
      </div>
    )
  }

  return (
    <>
      <div className="app_shell">
        {page === "landing" && (
          <LandingPage
            onLogin={() => setPage("login")}
            onRegister={() => setPage("register")}
          />
        )}

        {page === "login" && (
          <LoginPage
            onLogin={handleLogin}
            onGoRegister={() => setPage("register")}
          />
        )}

        {page === "register" && (
          <RegisterPage
            onRegistered={handleLogin}
            onGoLogin={() => setPage("login")}
          />
        )}

        {page === "dashboard" && user && (
          <DashboardPage
            user={user}
            onLogout={handleLogout}
           
          />
        )}

      

        {page === "ownerDashboard" && user && (
          <OwnerDashboard
            user={user}
            onLogout={handleLogout}
          />
        )}

        {page === "createSlot" && user && (
          <CreateSlotPage
            user={user}
            onBack={() => setPage("ownerDashboard")}
          />
        )}

        {page === "slotDetail" && user && (
          <SlotDetail
            user={user}
            onBack={() => setPage("ownerDashboard")}
          />
        )}
      </div>
    </>
  )
}

export default App
