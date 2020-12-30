
/*
 * Imports
 * -------
 */

import { 
  closest,
  hasClass,
  request
} from 'Formation/utils/utils';

/*
 * DOM Loaded
 * ----------
 */

const initialize = () => {

 /*
  * Get and set administrative divisions of country
  * -----------------------------------------------
  */

  const namespace = window.namespace;

  if( !window.hasOwnProperty( namespace ) )
    return;

  const n = window[namespace]; 

  if( !n.hasOwnProperty( 'geonames_un' ) )
    return;

  if( !n.geonames_un )
    return;

  /* Variables */

  let geonamesUrl = 'https://secure.geonames.org/',
      geonamesUsername = `&username=${ n.geonames_un }`;

 /*
  * Helper functions
  * ----------------
  */

  const disableStateSelect = ( select, disable ) => {
    select.disabled = disable;
    select.innerHTML = '<option value="">— Select —</option>';
  };

  const setCountryError = ( input, set ) => {
    let next = input.nextElementSibling;

    if( set ) {
      if( !hasClass( next, 'o-error' ) )
        input.insertAdjacentHTML( 'afterend', '<p class="o-error">Sorry, looks like the country you entered can\'t be found.</p>' );
    } else {
      if( next )
        next.parentNode.removeChild( next );
    }
  };

  const getAdmin1Inputs = ( countryInput ) => {
    let field = closest( countryInput, 'o-field' );

    return {
      countryCode: field.nextElementSibling.querySelector( 'input' ),
      countryId: field.nextElementSibling.nextElementSibling.querySelector( 'input' ),
      stateSelect: field.nextElementSibling.nextElementSibling.nextElementSibling.querySelector( 'select' ),
      state: field.nextElementSibling.nextElementSibling.nextElementSibling.nextElementSibling.querySelector( 'input' ),
      stateName: field.nextElementSibling.nextElementSibling.nextElementSibling.nextElementSibling.nextElementSibling.querySelector( 'input' )
    };
  };

  const setAdmin2 = ( args ) => {
    let options = '<option value="">— Select —</option>',
        countryCode = '',
        selected = args.selected || '';

    args.admin2.forEach( ( a, i ) => {
      if( i == 0 )
        countryCode = a.countryCode;

      let name = a.name,
          val = name,
          s = '';

      if( a.hasOwnProperty( 'adminCodes1' ) ) {
        if( a.adminCodes1.hasOwnProperty( 'ISO3166_2' ) ) {
          val = a.adminCodes1.ISO3166_2;

          if( selected == val )
            s = ' selected';
        }
      }

      options += '<option value="' + val + '"' + s + '>' + name + '</option>';
    } );

    args.codeInput.value = countryCode;
    args.stateSelect.innerHTML = options;
  };

  const getAdmin2 = ( args ) => {
    request( {
      method: 'POST',
      url: geonamesUrl + 'childrenJSON?geonameId=' + args.countryId + geonamesUsername
    } )
    .then( data => {
      let error = true;

      data = JSON.parse( data );

      if( data.hasOwnProperty( 'geonames' ) ) {
        if( data.geonames.length ) {
          error = false;
          disableStateSelect( args.stateSelect, false );
          setAdmin2( {
            codeInput: args.codeInput,
            stateSelect: args.stateSelect,
            admin2: data.geonames,
            selected: args.selected || ''
          } );
        }
      }

      if( error ) {
        disableStateSelect( args.stateSelect, true );
        setCountryError( args.countryInput, true );
      }
    } )
    .catch( xhr => {
      disableStateSelect( args.stateSelect, true );
      setCountryError( args.countryInput, true );
    } );
  };

  const getAdminLevel1 = ( input, country, name ) => {
    if( !country )
      return;

    country = encodeURIComponent( country );

    let inputs = getAdmin1Inputs( input ),
        codeInput = inputs.countryCode,
        idInput = inputs.countryId,
        stateSelect = inputs.stateSelect,
        stateInput = inputs.state;

    disableStateSelect( stateSelect, false );
    setCountryError( input, false );

    let url = geonamesUrl + 'searchJSON?name_equals=' + country + '&adminCode1=00&maxRows=1' + geonamesUsername;

    if( !name )
      url = geonamesUrl + 'countryInfoJSON?country=' + country + '&maxRows=1' + geonamesUsername;

    request( {
      method: 'POST',
      url: url
    } )
    .then( data => {
      let error = true;

      data = JSON.parse( data );

      console.log(data);

      if( data.hasOwnProperty( 'geonames' ) ) {
        if( data.geonames.length ) {
          error = false;

          let id = name ? data.geonames[0].countryId : data.geonames[0].geonameId;

          idInput.value = id;

          if( !name )
            input.value = data.geonames[0].countryName;

          getAdmin2( {
            countryId: id,
            countryInput: input,
            codeInput: codeInput,
            stateSelect: stateSelect,
            selected: stateInput.value
          } );
        }
      }

      if( error ) {
        disableStateSelect( stateSelect, true );
        setCountryError( input, true );
      }
    } )
    .catch( xhr => {
      disableStateSelect( stateSelect, true );
      setCountryError( input, true );
    } );
  };

 /*
  * Admin1 ( country ) input callback
  * ---------------------------------
  */

  window.getAdmin1 = ( event ) => {
    let input = event.currentTarget,
        country = input.value.trim();

    getAdminLevel1( input, country, true );
  };

 /*
  * Admin3 ( state/prov ) input callback 
  * ------------------------------------
  */

  window.setAdmin3Input = ( event ) => {
    let input = event.currentTarget,
        option = input.options[input.selectedIndex],
        field = closest( input, 'o-field' );

    if( !option || !field )
      return;

    let next = field.nextElementSibling;

    next.querySelector( 'input' ).value = option.value;
    next.nextElementSibling.querySelector( 'input' ).value = option.textContent;
  };

 /*
  * Set state select if country value 
  * ---------------------------------
  */

  let admin1 = [].slice.call( document.querySelectorAll( '.js-admin1' ) );

  if( admin1.length ) {
    admin1.forEach( ( a ) => {
      let inputs = getAdmin1Inputs( a ),
          codeInput = inputs.countryCode,
          idInput = inputs.countryId,
          stateSelect = inputs.stateSelect,
          stateInput = inputs.state;

      if( !idInput.value ) {
        getAdminLevel1( this, codeInput.value, false );
      } else {
        getAdmin2( {
          countryId: idInput.value,
          countryInput: this,
          codeInput: codeInput,
          stateSelect: stateSelect,
          selected: stateInput.value
        } );
      }
    } );
  }

}; // end initialize

document.addEventListener( 'DOMContentLoaded', initialize );
