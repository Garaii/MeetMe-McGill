/* ********************************************************************************************************* */
// OwnerDashboard.jsx
import { useState, useEffect } from 'react'
import Navbar from '../../components/Navbar'
import {apiGet, apiPost} from '../../api'

function OwnerDashboard({ user, onLogout }) {
  const [view, setView] = useState("slots")

  // Slots state
  const [slots, setSlots] = useState([])
  const [slotsLoading, setSlotsLoading] = useState(true)
 
  // Requests state
  const [requests, setRequests] = useState([])
  const [requestsLoaded, setRequestsLoaded] = useState(false)

  // Create slot form state
  const [slotDate, setSlotDate] = useState('')
  const [startTime, setStartTime] = useState('')
  const [endTime, setEndTime] = useState('')
  const [slotError, setSlotError] = useState('')
  const [slotSuccess, setSlotSuccess] = useState('')
  const [slotLoading, setSlotLoading] = useState(false)

  // Create group meeting form state
  const [meetingTitle, setMeetingTitle] = useState('')
  const [meetingDescription, setMeetingDescription] = useState('')
  const [meetingOptions, setMeetingOptions] = useState([
    { option_date: '', start_time: '', end_time: '' },
    { option_date: '', start_time: '', end_time: '' },
  ])
  const [meetingError, setMeetingError] = useState('')
  const [meetingSuccess, setMeetingSuccess] = useState('')
  const [meetingLoading, setMeetingLoading] = useState(false)

  // INVITATION URL STATE
  const [inviteUrl, setInviteUrl] = useState('')
  const [inviteCopied, setInviteCopied] = useState(false)


  // ======================= Fetch slots from php on mount
  useEffect(() => {
    apiGet('owner_slots.php')
      
      .then(data => {
        setSlots(data.slots || [])
        setSlotsLoading(false)
      })
      .catch(() => setSlotsLoading(false))


      //creating URL using curretn user's ID
      //Links to owner's booking page
    const base = window.location.hostname === "localhost"
    ? "http://localhost:5173"
    : window.location.origin
    setInviteUrl(`${base}/book/${user.id}`)


  }, [])

  // ======================== Fetch requests (only when tab opened) 
  const fetchRequests = () => {
    if (requestsLoaded) return
    apiGet('owner_requests.php')
      
      .then(data => {
        setRequests(data.requests || [])
        setRequestsLoaded(true)
        
      })
  }

  // ___________ COPY invite URL to CLipboard
    const handleCopyInvite = () => {
    navigator.clipboard.writeText(inviteUrl).then(() => {
      setInviteCopied(true)
      setTimeout(() => setInviteCopied(false), 2000)
    })
  }

  // Toggle slot active / private 
  const handleToggleVisibility = async (slot_id, current_status) => {
    try{ /*WILL HAVE TO UPDATE ENDPOINT NAME WHEN PHP PAGE BELOW IS DONE */
      await apiPost("update_slot_availability.php",{
      slot_id,
      is_active: current_status ? 0: 1
    })
    
      setSlots(prev =>
        prev.map(s => s.id === slot_id ? { ...s, is_active: current_status ? 0 : 1 } : s)
      )
    } catch (err){
      alert(err.message)
    }
    
  }

  // Delete slot , transforming into json
  const handleDeleteSlot = async (slot_id) => {
    if (!window.confirm('Delete this slot?')) return
    try{ //WILL HAVE TO CHANGE NAME WHEN PHP FINISH
      await apiPost("delete_slot.php", {slot_id: slot.id})

      // open mail to to notify slot is booked
      if (slot.booked_by_email) {
        window.location.href = `mailto:${slot.booked_by_email}?subject=Booking Cancelled&body=Hi, your booking slot has been deleted by the owner.`
      }
      setSlots(prev => prev.filter(s => s.id !== slot_id))
    } catch(err){
      alert(err.message)
    } 
  }

  // Email the person who booked a slot, not full email
  const handleEmailBookedUser = (slot) => {
    if (!slot.booked_by_email) return
    window.location.href = `mailto:${slot.booked_by_email}?subject=Regarding your booking`
  }

  // Accept request again transform into json for react
  const handleAcceptRequest = async (request_id) => {
    try{ //WILL HAVE TO CHANGE NAME WHEN PHP FINISH
      await apiPost("accept_request.php", {request_id})
    
      setRequests(prev =>
        prev.map(r => r.id === request_id ? { ...r, status: 'accepted' } : r)
      )
    } catch(err) {
      alert(err.message)
    }
  }

  // Decline request 
  const handleDeclineRequest = async (request_id) => {
    try { //WILL HAVE TO CHANGE NAME WHEN PHP FINISH
      await apiPost("decline_request.php", {request_id})
      setRequests(prev =>
        prev.map(r => r.id === request_id ? { ...r, status: 'declined' } : r)
      )
    } catch(err){
      alert(err.message)
    }
  }

  // Create single slot transforming to php -> json -> react
  const handleCreateSlot = async () => {
    setSlotError('')
    setSlotSuccess('')
    setSlotLoading(true)

    try{
      const data = await apiPost("create_slot.php", {
        slot_date: slotDate,
        start_time: startTime,
        end_time: endTime
      })
      setSlotSuccess(data.message)
      setSlotDate('')
      setStartTime('')
      setEndTime('')
      // Refresh slots list
      apiGet("owner_slots.php").then( d=> setSlots(d.slots || []))
    }catch(err) {
      setSlotError(err.message)
    } finally{
    setSlotLoading(false)}
  }

  //  Group meeting option helpers transforming to php -> json -> react
  const updateMeetingOption = (index, field, value) => {
    setMeetingOptions(prev =>
      prev.map((opt, i) => i === index ? { ...opt, [field]: value } : opt)
    )
  }

  const addMeetingOption = () => {
    setMeetingOptions(prev => [...prev, { option_date: '', start_time: '', end_time: '' }])
  }

  const removeMeetingOption = (index) => {
    setMeetingOptions(prev => prev.filter((_, i) => i !== index))
  }

  // Create group meeting transforming to php -> json -> react
  const handleCreateGroupMeeting = async () => {
    setMeetingError('')
    setMeetingSuccess('')
    setMeetingLoading(true)

    try{
        const data = await apiPost("create_group_meeting.php", {
          title: meetingTitle,
          description: meetingDescription,
          options: meetingOptions
        })
        setMeetingSuccess(data.message)
        setMeetingTitle('')
        setMeetingDescription('')
        setMeetingOptions([
          { option_date: '', start_time: '', end_time: '' },
          { option_date: '', start_time: '', end_time: '' },
        ])
      } catch(err) {
        setMeetingError(err.message)
      } finally {
        setMeetingLoading(false)
      }
  }

  // actual UI 
  return (
    <div>
      <Navbar onLogout={onLogout} user={user} />
      <div className="dashboard">
        <h1>Welcome back, {user.name}!</h1>

        {/* TABS */}
        <div className="dashboard-tabs">
          <button className="appt-tab-btn" onClick={() => setView("slots")}>
            My Slots
          </button>
          <button className="browse-tab-btn" onClick={() => { setView("requests"); fetchRequests() }}>
            Meeting Requests
          </button>
          <button onClick={() => setView("create_slot")}>
            Create Slot
          </button>
          <button onClick={() => setView("create_group")}>
            Create Group Meeting
          </button>
        </div>

        {/* == MY SLOTS == */}
        {view === "slots" && (
          <section className="appointments-view">
            <h2>My Slots</h2>
            {slotsLoading ? (
              <p>Loading...</p>
            ) : slots.length === 0 ? (
              <p>You have no slots yet.</p>
            ) : (
              <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Booked By</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {slots.map(slot => (
                    <tr key={slot.id}>
                      <td>{slot.slot_date}</td>
                      <td>{slot.start_time}</td>
                      <td>{slot.end_time}</td>
                      <td>{slot.is_active ? "Active" : "Private"}</td>
                      <td>{slot.booked_by ?? "Not booked"}</td>
                      <td>
                        <button onClick={() => handleToggleVisibility(slot.id, slot.is_active)}>
                          {slot.is_active ? "Make Private" : "Make Active"}
                        </button>
                        <button onClick={() => handleDeleteSlot(slot.id)}>
                          Delete
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        )}

        {/* ── MEETING REQUESTS ── */}
        {view === "requests" && (
          <section className="browse-view">
            <h2>Meeting Requests</h2>
            {!requestsLoaded ? (
              <p>Loading...</p>
            ) : requests.length === 0 ? (
              <p>No meeting requests.</p>
            ) : (
              <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>From</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {requests.map(req => (
                    <tr key={req.id}>
                      <td>{req.requester_name}</td>
                      <td>
                        <a href={`mailto:${req.requester_email}`}>{req.requester_email}</a>
                      </td>
                      <td>{req.message}</td>
                      <td>{req.status}</td>
                      <td>{req.created_at}</td>
                      <td>
                        {req.status === "pending" && (
                          <>
                            <button onClick={() => handleAcceptRequest(req.id)}>Accept</button>
                            <button onClick={() => handleDeclineRequest(req.id)}>Decline</button>
                          </>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        )}

        {/* ── CREATE SLOT ── */}
        {view === "create_slot" && (
          <div className="auth-page">
          <section className="auth-card">
            <h2>Create a Slot</h2>

            {slotError && <p className="auth-error">{slotError}</p>}
            {slotSuccess && <p className="auth-success">{slotSuccess}</p>}

            <div className="auth-form">
              <div className="form-group">
                <label>Date</label>
                <input type="date" value={slotDate} onChange={e => setSlotDate(e.target.value)} />
              </div>
              <div className="form-group">
                <label>Start Time</label>
                <input type="time" value={startTime} onChange={e => setStartTime(e.target.value)} />
              </div>
              <div className="form-group">
                <label>End Time</label>
                <input type="time" value={endTime} onChange={e => setEndTime(e.target.value)} />
              </div>

              <button className="btn-primary btn-full" onClick={handleCreateSlot} disabled={slotLoading}>
                {slotLoading ? 'Creating...' : 'Create Slot'}
              </button>
            </div>
          </section>
          </div>
        )}

        {/* ################## CREATE GROUP MEETING ################### */}
        {view === "create_group" && (
          <div className='auth-page'>
          <section className="auth-card">
            <h2>Create Group Meeting</h2>

            {meetingError && <p className="auth-error">{meetingError}</p>}
            {meetingSuccess && <p className="auth-success">{meetingSuccess}</p>}

            <div className="auth-form">
              <div className="form-group">
                <label>Meeting Title</label>
                <input
                  type="text"
                  placeholder="e.g. Office Hours Week 3"
                  value={meetingTitle}
                  onChange={e => setMeetingTitle(e.target.value)}
                />
              </div>
              <div className="form-group">
                <label>Description (optional)</label>
                <textarea
                  placeholder="Any details for attendees..."
                  value={meetingDescription}
                  onChange={e => setMeetingDescription(e.target.value)}
                  rows={3}
                />
              </div>

              <h3>Meeting Options</h3>
              {meetingOptions.map((opt, i) => (
                <div key={i} className="form-group meeting-option-row">
                  <label>Option {i + 1}</label>
                  <input type="date" value={opt.option_date} onChange={e => updateMeetingOption(i, 'option_date', e.target.value)} />
                  <input type="time" value={opt.start_time}  onChange={e => updateMeetingOption(i, 'start_time',  e.target.value)} />
                  <input type="time" value={opt.end_time}    onChange={e => updateMeetingOption(i, 'end_time',    e.target.value)} />
                  {meetingOptions.length > 1 && (
                    <button onClick={() => removeMeetingOption(i)}>Remove</button>
                  )}
                </div>
              ))}

              <button onClick={addMeetingOption}>+ Add Option</button>

              <button
                className="btn-primary btn-full"
                onClick={handleCreateGroupMeeting}
                disabled={meetingLoading}
              >
                {meetingLoading ? 'Creating...' : 'Create Group Meeting'}
              </button>
            </div>
          </section>
          </div>
        )}

        {/* BOTTOM ACTIONS  $$$$$$$$$$$$$$$$$$$$$$$$$$ maybe put this button elsewhere $$$$$$$$$$$$$$$$$$$$$$$$$$$$ */}
        <div className="dashboard-actions">
          <button onClick={onLogout}>Logout</button>
        </div>

      </div>
    </div>
  )
}

export default OwnerDashboard