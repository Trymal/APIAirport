var formLogin = document.querySelector('.form-login')
var MAP_API = {

	AVIATION_API_URL: "http://localhost/APIAirport/api/airports",
	LOGIN_API_URL: "http://localhost/APIAirport/api/user",

	infoWindow : null,
	map : null,
	airports: null,
	markers: [],
	userStatus: null,
	userToken: null,

	initMap : function () {

		/**
		 * Initalize the map with creating it and fetching data from AviationStack
		 */

		this.buildMap();
		this.fetchData();
		this.initFormLogin();
	},

	initFormLogin: function(){
		const form = document.querySelector('.form-login')

		form.addEventListener("submit", (e) => {
			e.preventDefault()
			const login = form.elements["login"].value;
			const password = form.elements["password"].value;
			this.login({login, password})
		})
	},

	buildMap : function () {

		/**
		 * Initialize map and its event
		 */
		
		this.map = new google.maps.Map(document.getElementById("map"), {
			center: { lat: 48.8534, lng: 2.3488 },
    		zoom: 5,
		});
		this.addAirport()

	},

	fetchData : async function (url = this.AVIATION_API_URL , initObject = {
		method: "GET",
		mode: 'cors',
		headers: new Headers({
			"Authorization": this.userToken
		})
	}) {

		/**
		 * Fetch data from API, empty markers and airports and append new elements
		 */
		fetch(url, initObject)
		.then(async (response) => {
			return await response.json()
		})
		.then(async (airports) =>  {
			this.airports = await airports.data
			const airportsList = document.querySelector("#airports-list")
			airportsList.innerHTML = ''
			this.markers.forEach(marker => {
				marker.setMap(null)
			});
			this.markers = []

			this.airports.forEach((airport) => {
				this.appendElementToList(airport)
			})
		})
	},

	/**
	 * Fetch API to login user
	 * @param { object } user 
	 * @return { void }
	 */
	 login(user){
		fetch(this.LOGIN_API_URL, {
			method: "POST",
			body: JSON.stringify(user)
		})
		.then(function(res){
			return res.json()
		})
		.then((data) => {
			if(data && data.code == 200){
				if(data.success){
					formLogin.innerHTML = `<h4 class="h4">Hi ${user.login} !</h4>`
					this.userStatus = data.status;
					this.userToken = data.token;
					this.addAirport();
					this.fetchData()
					return;
				}
				formLogin.querySelector('.alert').innerHTML = data.message
				formLogin.querySelector('.alert').classList.remove('d-none')
			}else{
				alert(data.message)
			}
		})
		.catch(function(error) {
			console.log('Error in fetch: ' + error.message);
		});
	},

	appendElementToList : function ( airport ) {

		/**
		 * Add an airport from data, with edit, delete buttons and associated infoWindows
		 */

		const airportsList = document.querySelector("#airports-list")

		//Element in airports list
		const el = document.createElement('li')
		el.style.display = 'flex'
		el.style.justifyContent = 'space-between'
		el.style.alignItems = 'center'
		el.innerHTML = `<span style="max-width:150px;overflow:hidden;">${airport.name}</span>
		<div style="display: flex;align-items: center;">
			<div class='edit'></div>
			<div class='trash'></div>
		</div>`
		
		const coords = { lat: parseFloat(airport.latitude), lng: parseFloat(airport.longitude)}
		
		if(['edit', 'admin'].includes(this.userStatus)){
			//Update form in infow Window
			const edit = document.createElement('div')
			edit.innerHTML = `<span style="cursor: pointer; margin-right: 5px;"><img width="20" src="./img/edit.svg"></span>`
	
			const update = document.createElement('form')
			update.style.display = 'flex'
			update.style.flexDirection = 'column'
			const content = `<input type="text" id="newAirport" name="changeAirport" value=${airport.name}>
					<input type="text" id="lat" name="lat" value="${coords.lat}">
					<input type="text" id="lng" name="lng" value="${coords.lng}">
					<button type="submit">Update</button>`
			update.innerHTML = content
			
			update.addEventListener('submit', (e) => {
				e.preventDefault()
				const name = update.elements.namedItem('changeAirport').value;
				const lat = update.elements.namedItem('lat').value;
				const lng = update.elements.namedItem('lng').value;
	
				let initObject = { 
					method: 'PUT',
					mode: 'cors',
					headers: new Headers({"Authorization": this.userToken}),
					body: JSON.stringify({
						name: name,
						latitude: lat,
						longitude: lng
					})
				};
				this.fetchData( this.AVIATION_API_URL + `?id=${airport.id}`, initObject );
				this.infoWindow.close()
			})
			edit.addEventListener('click', () => {
				if (this.infoWindow) this.infoWindow.close()
				this.infoWindow = new google.maps.InfoWindow({
					content: update,
					position: coords
				})
				this.infoWindow.open({
					map: this.map
				})
			})
			el.querySelector(".edit").appendChild(edit)
		}

		if(['admin'].includes(this.userStatus)){
			//Delete info Window
			const trash = document.createElement('div')
			trash.innerHTML = `<span style="cursor: pointer; margin-right: 5px;"><img width="20" src="./img/trash.svg"></span>`
	
			const deleteConfirm = document.createElement('div')
			const deleteButton = document.createElement('button')
			deleteConfirm.innerHTML = `
				<h1>Do you want to delete ${airport.name} ?</h1>
			`
			deleteButton.innerHTML = 'Confirm'
			deleteButton.addEventListener('click', () => {
				let initObject = { 
					method: 'DELETE',
					mode: 'cors',
					headers: new Headers({"Authorization": this.userToken}),
				};
				this.fetchData( this.AVIATION_API_URL + `?id=${airport.id}`, initObject );
				this.infoWindow.close()
			})
			deleteConfirm.appendChild(deleteButton)
			trash.addEventListener('click', () => {
				if (this.infoWindow) this.infoWindow.close()
				this.infoWindow = new google.maps.InfoWindow({
					content: deleteConfirm,
					position: coords
				})
				this.infoWindow.open({
					map: this.map
				})
			})
			el.querySelector(".trash").appendChild(trash)
		}

		airportsList.appendChild(el)
		el.addEventListener('click', () => {
			this.map.setCenter({ lat: parseFloat(airport.latitude), lng: parseFloat(airport.longitude)})
			this.map.zoom = 8
		})

		this.appendMarkerToMap(airport)

	},

	/**
	 * Form to add a new airport
	 */
	addAirport: function() {
		if (['edit', 'admin'].includes(this.userStatus)) {
			//Disable default double click
			this.map.setOptions({disableDoubleClickZoom: true})
			//Double click event
			this.map.addListener('dblclick', (e) => {
				const coords = e.latLng.toJSON()
				//Template
				const el = document.createElement("form");
				el.style.display = 'flex'
				el.style.flexDirection = 'column'
				const content = `<input type="text" id="newAirport" name="newAirport">
						<input type="text" id="lat" name="lat" value="${coords.lat}" disabled>
						<input type="text" id="lng" name="lng" value="${coords.lng}" disabled>
						<button type="submit">Add</button>`
				el.innerHTML = content
	
				//Info Window
				if (this.infoWindow) this.infoWindow.close()
				this.infoWindow = new google.maps.InfoWindow({
					content: el,
					position: coords
				})
				this.infoWindow.open({
					map: this.map
				})
				
				//Form event
				el.addEventListener('submit', (e) => {
					e.preventDefault()
					const name = el.elements.namedItem('newAirport').value;
					const lat = el.elements.namedItem('lat').value;
					const lng = el.elements.namedItem('lng').value;
	
					let initObject = { 
						method: 'POST',
						mode: 'cors',
						headers: new Headers({"Authorization": this.userToken}),
						body: JSON.stringify({
							name: name,
							latitude: lat,
							longitude: lng
						})
					};
					this.fetchData( this.AVIATION_API_URL, initObject );
					this.infoWindow.close()
				})
			})
		}
	},

	appendMarkerToMap: function( airport ) {

		/**
		 * Create a new Marker with custom Icon on the map
		 */

		//Custom icon
		icon = {
			url: "./img/plane.svg",
			scaledSize: new google.maps.Size(25,25),
			origin: new google.maps.Point(0,0),
			anchor: new google.maps.Point(0,0)
		}

		//Set Marker
		const marker = new google.maps.Marker({
			position: { lat: parseFloat(airport.latitude), lng: parseFloat(airport.longitude) },
			map: this.map,
			icon: icon
		})
		this.markers.push(marker)

		const contentBubble = `<div class="infoBubble">
		<p>Name : ${airport.name}</p>
		<p>Latitude : ${airport.latitude}</p>
		<p>Longitude : ${airport.longitude}</p>
		</div>`

		marker.addListener('click', () => {
			if (this.infoWindow) this.infoWindow.close()
			this.infoWindow = new google.maps.InfoWindow({
				content: contentBubble
			})
			this.infoWindow.open({
				anchor: marker,
				map: this.map
			})
		})

	}
}
