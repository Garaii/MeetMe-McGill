import { useState } from 'react'
import Navbar from '../components/Navbar'

// Dashboard.jsx
function DashboardPage({user, onLogout, onBook}) {
  const [view, setView] = useState("appointments")
  const [selectedOwner, setSelectedOwner] = useState(null)
  return (
  <div>
    <Navbar onLogout={onLogout} user={user} />
    <div className="dashboard">
    <h1>Welcome back, {user.name}! </h1>
    {/*LISTING TO SEE @MCGILL.CA WHO HAVE ACTIVE SLOTS
    * SELECT OWNER THEN SEE THEIR AVAILABLE SLOTS
    * AS A USER YOU CAN BOOK A SLOT
    * 
    * SEE THE SLOTS YOU HAVE BOOKED
    * -DELETE --> SLOT OWNER RECEIVE NOTIFICATION ':mailto'
    * -> slot goes back to available
    * - MESSAGE THE OWNER OF THE SLOT
    * 
    * Users see a dashboard listing their appointments and providing the following options:
    ▪ They can email (mailto:) the owner of a slot (selected or open slot)
    ▪ They can delete appointments.
    ▪ They can book additional appointments.
    ▪ They can logout
    * 
    * 
    */}
    {/* INSTEAD OF HAVING DIFFERENT PAGES WILL HAVE TABLES INSIDE OF DASHBOARD*/}
                      {/*MY APPOINTMENTS*/}
    <div className="dashboard-tabs">
      <button onClick={() => setView("appointments")}>My Appointments</button>
      <button onClick={() => setView("browse")}>Browse Owners</button>
    </div>
     
      {view === "appointments" && (
        <section className='appointments-view'>
          <h2>My Appointments</h2>
           
          {/* appointment block with
              ACTIONS: 
              - mailto owner of slot
              - Delete
              DETAILS: TIMES, DATE, OWNER NAME, 2 BUTTONS FROM THE ACTIONS ABOVE
              THIS IS FOR EXISTING APPOINTMENTS, HAVE TO MAP THEM TO BACK END
          */}

          <p> NO APPOINTMENTS.</p>
        </section>
      )}

      {/* NEW APPOINTMENTS */}
      {view === "browse" && (
          <section className="browse-view">
            <h2>Browse Owners</h2>
            {/* fetch list of @mcgill.ca owners with active slots */}
            {/* DISPLAY AS ROW OR CARDS: owner name, num slots, button to view their slots */}
            <p>No owners available.</p>
          </section>
      )}

      {view === "book" && selectedOwner && (
        <section className="book-view">
          <h2>Book a slot with {selectedOwner.name}</h2>
          <button onClick={() => setView("browse")}>← Back to browse</button>
          {/* fetch active slots for this owner FROM BACKEND */}
          {/* each slot: date, time, type, book button */}
        </section>
      )}

   
    {/* APPOINTMENT ACTIONS*/}
    <div className="dashboard-actions">
      <button onClick={onBook} className="btn-primary">BOOK AN APPOINTMENT</button>
      <button onClick={onLogout}>Logout</button>
    </div>
    </div>
  </div>
    
  )
}
export default DashboardPage