<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>Criador de Modelos</title>

        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
        <link rel="stylesheet" href="css/estilo_index.css">
    </head>
    <style>
    </style>
    <body>
        <div id="amostras">
            <div class="modelo">
                <img draggable="false" src="img/exemplo_01.png">
            </div>
            <div class="modelo">
                <img draggable="false" src="img/exemplo_01.png">
            </div>
            <div class="modelo">
                <img draggable="false" src="img/exemplo_01.png">
            </div>
            <div class="modelo">
                <img draggable="false" src="img/exemplo_01.png">
            </div>
            <div class="modelo">
                <img draggable="false" src="img/exemplo_01.png">
            </div>
            <div class="modelo">
                <img draggable="false" src="img/exemplo_01.png">
            </div>
        </div>
        <div id="transforme">
            Objeto
            <div id="posicao">
                Posicao: <br>
                x: <input id="x" type="number">
                y: <input id="y" type="number">
                z: <input id="z" type="number">
            </div>
            <div id="rotacao">
                Rotacao: <br>
                x: <input id="x" type="number">
                y: <input id="y" type="number">
                z: <input id="z" type="number">
            </div>
            <div>
                cor:
                <input id="cor" type="color" value="#ffffff">
            </div>
        </div>
        <div id="console" class><span id="msg" class="oculto"></span></div>
        <button id="adicionar">+</button>
        <script type="importmap">
            {
                "imports" : {
                    "three" :  "./node_modules/three/build/three.module.js"
                }
            }
        </script>
        <script type='module'>
            import * as THREE from 'three';
            import { GLTFLoader } from './node_modules/three/examples/jsm/loaders/GLTFLoader.js';
            import { OrbitControls } from './node_modules/three/examples/jsm/controls/OrbitControls.js';
            import { EffectComposer } from './node_modules/three/examples/jsm/postprocessing/EffectComposer.js';
            import { RenderPass } from './node_modules/three/examples/jsm/postprocessing/RenderPass.js';
            import { OutlinePass } from './node_modules/three/examples/jsm/postprocessing/OutlinePass.js';
            import { FilmPass } from './node_modules/three/examples/jsm/postprocessing/FilmPass.js';


            class Cookie {
                static addCookie(nome, valor){ document.cookie = nome + '=' + valor + '; path=/'; }
                static getIsCookie(nome){ return (document.cookie.split('; ').find(row => row.startsWith(nome + '='))?.split('=')[1].length > 0) ? true : false; }
                static getCookie(nome){ return document.cookie.split('; ').find(row => row.startsWith(nome + '='))?.replace(nome + '=', ''); }
                static setCookie(nome, valor){ document.cookie = nome + '=' + valor + '; path=/'; return true; }
                static clearCookie(nome){ document.cookie = nome + '=;path=/;'; }
                static deleteCookie(nome){
                    var data = new Date();
                    // Converte a data para GMT
                    // Apaga o cookie
                    document.cookie = nome + '=; expires=Mon, 18 Dec 1995 17:28:35 GMT; path=/';
                }
                static countRow(nome){ if(this.getIsCookie(nome)){ var texto =  this.getCookie(nome); var remover = texto.split('</br>'); return (remover.length + 1); } else { return 0; }}
            }
            
            function console(msg){
                var console = document.getElementById("msg");
                console.innerHTML = msg;
                console.className = 'visivel';
                setTimeout(function(){
                    console.className = 'oculto';
                }, 1000); 
            }

            function convertObjString(id, obj){
                var cor = new THREE.Color();
                cor = obj.material.color;
                var infoObj = "<n>"  +   obj.name  +   "<n>";
                infoObj +=    "<l>"  +   obj.type  +   "<l>";
                infoObj += "<p><x>" + obj.position.x + "<x>";
                infoObj +=    "<y>" + obj.position.y + "<y>"; 
                infoObj +=    "<z>" + obj.position.z + "<z><p>";
                infoObj += "<r><x>" + obj.rotation.x + "<x>";
                infoObj +=    "<y>" + obj.rotation.y + "<y>"; 
                infoObj +=    "<z>" + obj.rotation.z + "<z><r>";
                infoObj +=    "<cl>" + cor.getHexString() + "<cl>";
                
                Cookie.addCookie("objeto_" + id, infoObj);
            }

            function convertGrupoString(grupo) {
                var info = "<n>"+ grupo.name + "<n>";
                
                info += "<p><x>" + grupo.position.x + "<x>";
                info +=    "<y>" + grupo.position.y + "<y>"; 
                info +=    "<z>" + grupo.position.z + "<z><p>";
                info += "<r><x>" + grupo.rotation.x + "<x>";
                info +=    "<y>" + grupo.rotation.y + "<y>"; 
                info +=    "<z>" + grupo.rotation.z + "<z><r>";

                Cookie.addCookie("objetoPai", info);

                if(grupo.children.length > 0){
                    for (let i = 0; i < grupo.children.length; i++) {
                        convertObjString(i, grupo.children[i]);
                    }
                }
                else {
                    console(grupo.children.length);
                    Cookie.addCookie("objetoPai", "");
                }
            }

            function grid(){
                let points = [];

                for (let z = 0; z < 32; z++) {
                    for (let x = 0; x < 32; x++) {
                            points.push( new THREE.Vector3( x, 0, z));
                            points.push( new THREE.Vector3( x + 1, 0, z));
                            points.push( new THREE.Vector3( x + 1, 0, z + 1 ));
                            points.push( new THREE.Vector3( x, 0, z + 1 ));
                            points.push( new THREE.Vector3( x, 0, z));
                    }
                    points.push( new THREE.Vector3( 7, 0, z));
                    points.push( new THREE.Vector3( 7, 0, z + 1));
                }

                return points;
            }

            function cor(){
                return document.querySelector("#cor").value;
            }

            // VARIAVEIS GLOBAIS
            var LARGURA_JANELA = window.innerWidth, ALTURA_JANELA = window.innerHeight;
            var cena, camera, render, composer, outline, controles, mouse;
            var pontoIntersecao, carregadorModelos, copiaObjeto, objetoPai, links, objetosSelecionados, infoObjeto
            var criar = false, control = false, shilft = false, mover = false, girar = false, eixo = '';

            // FUNÇÕES DE COMANDO
            Iniciar();
            Atualizar();

            // FUNÇÕES QUE SÃO EXECUTADAS APENAS UMA VEZ NO INICIO
            function Iniciar(){
                configurarCena();
                configurarCamera();
                configurarRender();
                configurarEfeitos();
                configurarControles(cena, render);
                carregarIluminacao();
                carregarObjetos();
            }

            function configurarCena(){
                cena = new THREE.Scene();
                cena.background = new THREE.Color( '#494949' );
            }

            function configurarCamera(){
                let viewSize = 300;
                let aspectRatio = LARGURA_JANELA / ALTURA_JANELA;
                camera = new THREE.PerspectiveCamera( 70, LARGURA_JANELA / ALTURA_JANELA, 0.1, 1000 );
                //camera = new THREE.OrthographicCamera( - aspectRatio * viewSize / 150, aspectRatio * viewSize / 150,  viewSize / 150, -viewSize / 150, -1000, 1000);
                camera.position.z = 3;
            }

            function configurarRender(){
                render = new THREE.WebGLRenderer( { antialias: true } );
                render.setSize(LARGURA_JANELA, ALTURA_JANELA);
                render.physicallyCorrectLights = true;
                render.outputEncoding = THREE.sRGBEncoding;
                render.setPixelRatio( window.devicePixelRatio );
                render.toneMapping = THREE.CineonToneMapping;
                render.toneMappingExposure = 2;
                render.shadowMap.enabled = true;
                render.shadowMap.type = THREE.PCFSoftShadowMap;

                document.body.appendChild( render.domElement );

                window.addEventListener( 'resize', function() {
                    LARGURA_JANELA = window.innerWidth;
                    ALTURA_JANELA = window.innerHeight;
                    
                    camera.aspect = LARGURA_JANELA / ALTURA_JANELA ;
                    camera.updateProjectionMatrix();
                    render.setSize( LARGURA_JANELA, ALTURA_JANELA )
                });
            }

            function configurarEfeitos(){
                composer = new EffectComposer( render );

                var renderPass = new RenderPass( cena, camera );
                composer.addPass( renderPass );
                render.renderToScreen = true;

                outline = new OutlinePass( new THREE.Vector2( LARGURA_JANELA, ALTURA_JANELA), cena, camera );
                outline.edgeStrength = Number(10);
                outline.edgeGlow = Number(0);
                outline.edgeThickness = Number(1),
                outline.pulsePeriod = Number(0);
                outline.visibleEdgeColor.set( "#ffffff" );
                outline.hiddenEdgeColor.set( "#000000" );
                outline.usePatternTexture = false;
                outline.renderToScreen = true;
                composer.addPass( outline );

                var filmPass = new FilmPass(
                    0.35,
                    0.5,
                    648,
                    0
                );
                filmPass.renderToScreen = true;
                //composer.addPass(filmPass);
            }

            function configurarControles(cena, render){
                controles = new OrbitControls( camera, render.domElement );

                mouse = new THREE.Vector2();
                
                var planoNormal = new THREE.Vector3();
                var plano = new THREE.Plane();
                var raycaster = new THREE.Raycaster();
                objetosSelecionados = new Array();
                pontoIntersecao = new THREE.Vector3();

                window.addEventListener( 'mousemove', function(e) { 
                    mouse.x = ( e.clientX / LARGURA_JANELA ) * 2 - 1;
                    mouse.y = - ( e.clientY / ALTURA_JANELA ) * 2 + 1;
                    planoNormal.copy( camera.position ).normalize();
                    plano.setFromNormalAndCoplanarPoint( planoNormal, cena.position );
                    raycaster.setFromCamera( mouse, camera );
                    raycaster.ray.intersectPlane( plano, pontoIntersecao );
                    if(objetosSelecionados[0] !== undefined) {
                        infoObjeto.children[0].children[1].value = objetosSelecionados[0].position.x * 10;
                        infoObjeto.children[0].children[2].value = objetosSelecionados[0].position.y * 10;
                        infoObjeto.children[0].children[3].value = objetosSelecionados[0].position.z * 10;
                        if (mover == true) {
                            var mov = new THREE.Vector3();
                            mov.copy(pontoIntersecao);
                            switch (eixo) {
                                case 'x':
                                    objetosSelecionados[0].position.setX(mov.x);
                                    break;
                
                                case 'y':
                                    objetosSelecionados[0].position.setY(mov.y);
                                    break;
                
                                case 'z':
                                    objetosSelecionados[0].position.setZ(mov.z);
                                    break;
                
                                default:
                                    objetosSelecionados[0].position.copy(mov);
                                    break;
                            }
                        }
                    }        
                } );

                window.addEventListener( 'click', function  () {
                    if (criar == true) {
                        criarObjeto();
                    }
                })

                document.body.addEventListener( 'mousedown', selecionarObjeto);
                
                document.getElementById("adicionar").onclick = (event) => {
                    setTimeout(function(){
                        criar = true;
                    }, 1); 
                };

                infoObjeto = document.getElementById("transforme");
                infoObjeto.addEventListener( "input" , function (e) {
                    if(objetosSelecionados.length > 0) {
                        objetosSelecionados[0].position.setX(infoObjeto.children[0].children[1].value / 10);
                        objetosSelecionados[0].position.setY(infoObjeto.children[0].children[2].value / 10);
                        objetosSelecionados[0].position.setZ(infoObjeto.children[0].children[3].value / 10);
                    }
                });

                cor = document.getElementById("cor");
                cor.addEventListener( "input" , function (e) {
                    if(objetosSelecionados.length > 0) {
                        var mat = new THREE.MeshPhongMaterial( { color: e.target.value } );
                        objetosSelecionados[0].material = mat;  
                    }
                }, false);

                window.addEventListener("keydown", event => {
                    switch (event.code) {
                        case "Delete":
                            objetoPai.remove(objetosSelecionados[0]);
                            objetosSelecionados = [];
                            outline.selectedObjects = objetosSelecionados;
                            convertGrupoString(objetoPai);
                            break;

                        case "KeyF":
                            camera.lookAt(objetosSelecionados[0].position);
                            break;

                        case "KeyG":
                            mover = true;
                            girar = false;
                            break;
                        
                        case "KeyR":
                            girar = true;
                            mover = false;
                            break;
                        
                        case "KeyX":
                            eixo = 'x';
                            break;
                        
                        case "KeyY":
                            eixo = 'y';
                            break;    
                        
                        case "KeyZ":
                            eixo = 'z';
                            break;    

                        case "Enter":
                            mover = false;
                            girar = false;
                            break;    

                        default:
                            control = false;
                            shilft = false;
                            break;
                    }
                    if(event.ctrlKey && (event.key === "c")){
                        if(objetosSelecionados[0] !== undefined) {
                            copiaObjeto = objetosSelecionados[0];
                        }
                    }
                    if(event.ctrlKey && (event.key === "v")){
                        if (copiaObjeto !== null) {
                            copiarObjeto();
                        }
                    }
                    if(event.ctrlKey && (event.key === "y")){
                        salvar(objetoPai);
                    }
                });
            }

            function carregarIluminacao() {
                var luzAmbiente = new THREE.AmbientLight( { color: 'white' }, .5 );
                cena.add( luzAmbiente );

                var luzDirecional = new THREE.DirectionalLight( { color: 'white' }, 1 );

                luzDirecional.position.z += 2;
                luzDirecional.position.set(0, 1, 1);
                luzDirecional.castShadow = true;
                luzDirecional.shadow.normalBias = .05;
                luzDirecional.shadowCameraVisible = true;
                luzDirecional.shadowDarkness = 0.5;
                luzDirecional.shadow.mapSize.set(20, 20);

                cena.add( luzDirecional );   
            }

            function carregarObjetos(){
                carregadorModelos = new GLTFLoader();

                var guias = new THREE.AxesHelper( 20 );
                cena.add( guias );

                let material = new THREE.LineBasicMaterial( { color: 0x0f0f0f } );
                let geometria = new THREE.BufferGeometry().setFromPoints( grid() );

                let grade = new THREE.Line( geometria, material );
                grade.position.x -= 16;
                grade.position.z -= 16;
                cena.add(grade);

                objetoPai = new THREE.Group();
                objetoPai.name = "Modelo";
                cena.add( objetoPai );    

            }

            // FUNÇÕES QUE SÃO EXECUTADAS VARIAS VEZES DURANTE A EXECUÇÃO
            export function Atualizar(){
                requestAnimationFrame( Atualizar );
                // ANIMACAO INICIO


                
                // ANIMACAO FIM
                render.render( cena, camera );
                composer.render();
            }

            function selecionarObjeto(e){
                switch (e.buttons) {
                    case 1:
                        mover = false;
                        eixo = '';
                        var raycaster = new THREE.Raycaster();
                        raycaster.setFromCamera( mouse, camera, );
                        var intersecao = raycaster.intersectObjects( objetoPai.children );
                        if ( intersecao.length > 0 ) {
                            objetosSelecionados = [];
                            objetosSelecionados.push(intersecao[0].object);
                            outline.selectedObjects = objetosSelecionados;
                        }       
                        break;
                    default:
                        break;
                }
            }

            function criarObjeto(){
                var material = new THREE.MeshPhongMaterial( { color: "white" } );
                carregadorModelos.load( './resource/blocos/BlocoT2.glb', function ( gltf ) {
                    gltf.scene.children[0].type = './resource/blocos/BlocoT2.glb';
                    gltf.scene.children[0].material = material;
                    gltf.scene.children[0].position.copy( pontoIntersecao );
                    objetosSelecionados = [];
                    objetosSelecionados.push(gltf.scene.children[0]);
                    outline.selectedObjects = gltf.scene.children[0];
                    objetoPai.add( gltf.scene.children[0] );
                    criar = false;
                },  
                undefined, function ( error ) {
                    console.error( error );    
                }
                );
            }

            function copiarObjeto(){
                carregadorModelos.load( './resource/blocos/BlocoT2.glb', function ( gltf ) {
                    gltf.scene.children[0].type = './resource/blocos/BlocoT2.glb';
                    gltf.scene.children[0].material = copiaObjeto.material;
                    gltf.scene.children[0].position.copy( copiaObjeto.position );
                    objetosSelecionados = [];
                    objetosSelecionados.push(gltf.scene.children[0]);
                    outline.selectedObjects = gltf.scene.children[0];
                    objetoPai.add( gltf.scene.children[0] );
                    criar = false;
                }, 
                undefined, function ( error ) {
                    console.error( error );    
                }
                );
            }

            function carregarModelo(nome, link, posicao, rotacao, cor){
                var material = new THREE.MeshPhongMaterial( { color: "#" + cor } );
                carregadorModelos.load('./resource/blocos/BlocoT2.glb', function ( gltf ) {
                    gltf.scene.children[0].name = nome;
                    gltf.scene.children[0].type = link;
                    gltf.scene.children[0].material = material;

                    gltf.scene.children[0].position.setX(Number(posicao.split("<x>")[1]));
                    gltf.scene.children[0].position.setY(Number(posicao.split("<y>")[1]));
                    gltf.scene.children[0].position.setZ(Number(posicao.split("<z>")[1]));

                    gltf.scene.children[0].rotation.x = Number(rotacao.split("<x>")[1]);
                    gltf.scene.children[0].rotation.y = Number(rotacao.split("<y>")[1]);
                    gltf.scene.children[0].rotation.z = Number(rotacao.split("<z>")[1]);

                    objetoPai.add( gltf.scene.children[0] );
                    
                },  
                undefined, function ( error ) {
                    alert("erro");   
                }
                );
            }

            function salvar(grupo){
                convertGrupoString(grupo);
                window.location.href = "salvar.php";
            }
        
        <?php
            include('./lib.php');
            $con = new Conexao("localhost", "root", "admin", "db_ponto");
            $con->conectar();

            $linhas = $con->query("SELECT nome, tipo, posicao, rotacao, cor FROM db_pontos.objeto WHERE idPai = 0;");

            for ($i=0; $i < count($linhas); $i++) { 
                echo "carregarModelo('".$linhas[$i]['nome']."', '".$linhas[$i]['tipo']."', '".$linhas[$i]['posicao']."', '".$linhas[$i]['rotacao']."', '".$linhas[$i]['cor']."');";
            }
        ?>
        </script>
    </body>
</html>