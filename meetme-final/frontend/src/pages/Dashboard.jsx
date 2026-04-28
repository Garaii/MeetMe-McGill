import { useState } from 'react'
import Navbar from '../components/Navbar'
import {apiGet, apiPost} from '../../../api'

// Dashboard.jsx
function DashboardPage({user, onLogout /*, onBook*/}) {
  const [view, setView] = useState("appointments")
  const [selectedOwner, setSelectedOwner] = useState(null)

   // My CURRENT bookings
  const [bookings, setBookings] = useState([])
  const [bookingsLoading, setBookingsLoading] = useState(true)
  const [bookingsError, setBookingsError] = useState("")
 
  // Browse owners FOR OPEN SLOTS
  const [owners, setOwners] = useState([])
  const [ownersLoading, setOwnersLoading] = useState(false)
  const [ownersLoaded, setOwnersLoaded] = useState(false)
 
  // Owner slots (book view)
  const [ownerSlots, setOwnerSlots] = useState([])
  const [ownerSlotsLoading, setOwnerSlotsLoading] = useState(false)
  const [bookMessage, setBookMessage] = useState("")

  /* ===================== fething bookings ====================== */
  useEffect(()=>{
    apiGet("dashboard.php")
      .then(data => {
        setBookings(data.user_bookings || [])
        setBookingsLoading(false)
      })

      .catch(err => {
        setBookingsError(err.message)
        setBookingsLoading(false)
      })

  },[])

  /* ======================== FETCH OWNERS LIST (BUT LOADED LAZY SO CHANGE IT)====================== */
  const fetchOwners = () => {
    if (ownersLoaded) return
    setOwnersLoading(true)
    apiGet("owners_list.php")
      .then(data => {
        setOwners(data.owners || [])
        setOwnersLoading(false)
        setOwnersLoaded(true)
      })
      .catch(() => setOwnersLoading(false))

  }

/* ++++++++++++++++++++++++++++++++ FETCH SLOTS FROM SELECTED OWNER ++++++++++++++++++++++++++++++++++++ */
  const handleSelectOwner = (owner) => {
    setSelectedOwner(owner)
    setOwnerSlots([])
    setBookMessage("")
    setOwnerSlotsLoading(true)
    setView("book")

    apiGet('owner_booking_page.php?owner_id=${owner.id}')
      .then(data => {
        setOwnerSlots(data.slots || [])
        setOwnerSlotsLoading(false)
      })

      .catch(() => setOwnerSlotsLoading(false))

  }

  /* _________________________________________ BOOK SLOT FUNCTION _______________________________________________ */

  const handleBookSlot = async (slot_id) => {
    setBookMessage("")
    try{
      //WILL HAVE TO CHANGE NAME WHEN PHP FINISH
      await apiPost("book_slot", {slot_id})
      setBookMessage("Slot booked sucessfully!")
      setOwnerSlots(prev => prev.filter (s => s.id !== slot_id))

      //making sure to update the bookings list
      apiGet("dashboard.php").then( d => setBookings(d.user_bookings || []))
    } catch (err) {
      setBookMessage(err.message)
    }
  }

  /* _______________________________________ CANCELING BOOKING FUNCTION __________________________________ */
  const handleCancelBooking = async (booking) => {
    // will have to update appearance of this
    if(!window.confirm("Cancel this booking?")) return
    try {
      const data = await apiPost("cancel_booking.php", { booking_id: booking.booking_id})

      if(data.owner_email){
        window.location_href= `mailto:${data.owner_email}?subject=Booking Cancelled&body=Hi, I have cancelled my booking.`
      }

      setBookings( prev => prev.filter (b=> b.booking_id !== booking.booking_id))

    } catch (err) {
      alert(err.message)
    }
  }



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
    
    {/* ................... TABS ..................*/}       
    
    <div className="dashboard-tabs">
      <button 
        className="appt-tab-btn"
        onClick={() => setView("appointments")}
      >
        My Appointments
      </button>
      <button 
        className="browse-tab-btn" 
        onClick={() => setView("browse")}
      >
        Browse Owners
      </button>
    </div>
     
    {/* ___________________________ MY APPOINTMENTS _______________________*/}
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

        {/* fetching for bookings, loading */}
        {bookingsLoading && <p> Loading... </p>}
        {bookingsError && <p className='auth-error'>{bookingsError}</p>}

        {/* if no bookings found we tell student they have none */}
        {!bookingsLoading && bookings.length === 0 && (
          <p>No appointments yet.</p>
        )} 

        {/* HERE TO DISPLAY BOOKINGS AS A TABLE, WILL HAVE TO STYLE */}
        {!bookingsLoading && bookings.length > 0 && (
          <table className='dashboard-table'>
            <thead>
              <tr>
                <th>Date</th>
                <th>Start</th>
                <th>End</th>
                <th>Owner</th> 
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              {bookings.map(booking => (
                <tr key={booking.booking_id}>
                  <td>{booking.slot_date}</td>
                  <td>{booking.start_time}</td>
                  <td>{booking.end_time}</td>
                  <td>{booking.owner_name}</td>

                  <td>
                    <a href={`mailto:${booking.owner_email}`}>
                      <button>Email Owner</button>
                    </a>
                    <button onClick={()=> handleCancelBooking(booking)}>
                      Cancel
                    </button>

                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}

      </section>
    )}

    {/* NEW APPOINTMENTS ------------- BROWSE OWNERS */}
    {view === "browse" && (
        <section className="browse-view">
          <h2>Browse Owners</h2>
          {/* fetch list of @mcgill.ca owners with active slots */}
          {/* DISPLAY AS ROW OR CARDS: owner name, num slots, button to view their slots */}
          {ownersLoading && <p>Loading....</p>}

          {/* _________________ no owners with slots open _______________*/}
          {!ownersLoading && ownersLoaded && owners.length === 0 && (
            <p>No one with active slots right now.</p>
          )}

          {/* _______________________________________ */}
          {!ownersLoading && owners.length > 0 && (
            <div className="owners-list">
              {owners.map(owner => (
                <div key={owner.id} className='owner-card'>
                  <div>
                    <strong>{owner.name}</strong>
                    <span>{owner.email}</span>
                </div>
                <button
                  className='btn-primary'
                  onClick={() => handleSelectOwner(owner)}
                >
                  View Slots
                </button>
                </div>

              ))}
            </div>
          )}
        </section>
    )}

    {/* we did view not we want tto actually book slots */}


    {/* ________________________ BOOK SLOTS ______________________ */}
    {view === "book" && selectedOwner && (
      <section className="book-view">
        
        <button onClick={() => setView("browse")}>← Back to browse</button>
        <h2>Available slots with {selectedOwner.name}</h2>
        
        {/* fetch active slots for this owner FROM BACKEND */}
        {/* each slot: date, time, type, book button */}

        {bookMessage && <p className='form-message'>{bookMessage}</p>}
        {ownerSlotsLoading && <p>Loading slots...</p>}

        {!ownerSlotsLoading && ownerSlots.length === 0 && (
          <p>No available slots for this person</p>
        )}

        {/*______LOADING TABLE OF OWNER AVAILABILITIES_______ */}
        {!ownersLoading && ownerSlots.length > 0 && (
          <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {ownerSlots.map(slot => (
                    <tr key={slot.id}>
                      <td>{slot.slot_date}</td>
                      <td>{slot.start_time}</td>
                      <td>{slot.end_time}</td>
                      <td>
                        <button
                          className="btn-primary"
                          onClick={() => handleBookSlot(slot.id)}
                        >
                          Book
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
        )}
      </section>
    )}



   
    {/* APPOINTMENT ACTIONS*/}
    <div className="dashboard-actions">
      <button onClick={onLogout}>Logout</button>
    </div>
    </div>
  </div>
    
  )
}
export default DashboardPage