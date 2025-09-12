
import React, { useState, useEffect, useRef } from 'react';
import * as THREE from 'three';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

const AnimalCard = ({ animal, API_BASE, onStatusUpdate, getImageUrl, getModelUrl, getConservationColor }) => {
  const [isLoading, setIsLoading] = useState(false);
  const [statusMessage, setStatusMessage] = useState('');
  const [isLoading3D, setIsLoading3D] = useState(false);
  const [has3DModel, setHas3DModel] = useState(false);
  const [imageError, setImageError] = useState(false);
  const [modelError, setModelError] = useState(false);
  const [downloadUrl, setDownloadUrl] = useState('');
  const viewerRef = useRef(null);
  const sceneRef = useRef(null);

  // Get animal image from API data
  const getAnimalImage = (animal) => {
    if (animal.images && animal.images.length > 0) {
      const primaryImage = animal.images.find(img => img.is_primary) || animal.images[0];
      return `http://localhost/3d_web/animal-showcase/${primaryImage.path}`;
    }
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjMzM0MTU1Ii8+CjxwYXRoIGQ9Ik0xMDAgNjBDMTE2LjU2OSA2MCAxMzAgNzMuNDMxIDEzMCA5MEMxMzAgMTA2LjU2OSAxMTYuNTY5IDEyMCAxMDAgMTIwQzgzLjQzMSAxMjAgNzAgMTA2LjU2OSA3MCA5MEM3MCA3My40MzEgODMuNDMxIDYwIDEwMCA2MFoiIGZpbGw9IiM2QjcyOEEiLz4KPHBhdGggZD0iTTEwMCAxNDBDMTE2LjU2OSAxNDAgMTMwIDE1My40MzEgMTMwIDE3MEMxMzAgMTg2LjU2OSAxMTYuNTY5IDIwMCAxMDAgMjAwQzgzLjQzMSAyMDAgNzAgMTg2LjU2OSA3MCAxNzBDNzAgMTUzLjQzMSA4My40MzEgMTQwIDEwMCAxNDBaIiBmaWxsPSIjNkI3MjhBIi8+CjxwYXRoIGQ9Ik0xNDAgMTAwQzE0MCA4My40MzEgMTUzLjQzMSA3MCAxNzAgNzBDMTg2LjU2OSA3MCAyMDAgODMuNDMxIDIwMCAxMDBDMTk5LjkgMTE2LjU2OSAxODYuNTY5IDEzMCAxNzAgMTMwQzE1My40MzEgMTMwIDE0MCAxMTYuNTY5IDE0MCAxMDBaIiBmaWxsPSIjNkI3MjhBIi8+CjxwYXRoIGQ9Ik0zMCAxMDBDMzAgODMuNDMxIDQzLjQzMSA3MCA2MCA3MEM3Ni41NjkgNzAgOTAgODMuNDMxIDkwIDEwMEM5MCAxMTYuNTY5IDc2LjU2OSAxMzAgNjAgMTMwQzQzLjQzMSAxMzAgMzAgMTE2LjU2OSAzMCAxMDBaIiBmaWxsPSIjNkI3MjhBIi8+Cjwvc3ZnPgo=';
  };

  // Tạo download URL khi component mount hoặc animal.models thay đổi
  useEffect(() => {
    if (animal.models && animal.models.length > 0) {
      const model3D = animal.models[0];
      const downloadUrl = `http://localhost/3d_web/animal-showcase/${model3D.path}`;
      setDownloadUrl(downloadUrl);
    }
  }, [animal.models]);

  // Kiểm tra model 3D có sẵn từ cấu trúc media mới
  useEffect(() => {
    if (animal.models && animal.models.length > 0) {
      setHas3DModel(true);
      const model3D = animal.models[0];
      // Đợi DOM render hoàn toàn trước khi load
      setTimeout(() => {
        if (viewerRef.current) {
          console.log('✅ viewerRef is ready, loading 3D model...');
          load3DModel(`http://localhost/3d_web/animal-showcase/${model3D.path}`);
        } else {
          console.log('⏳ viewerRef not ready yet, retrying...');
          // Thử lại sau 100ms
          setTimeout(() => {
            if (viewerRef.current) {
              console.log('✅ viewerRef ready on retry, loading 3D model...');
              load3DModel(`http://localhost/3d_web/animal-showcase/${model3D.path}`);
            } else {
              console.log('❌ viewerRef still not ready after retry');
            }
          }, 100);
        }
      }, 50);
    } else {
      setHas3DModel(false);
    }
  }, [animal.models]);

  // Handle window resize - chỉ resize renderer, không reload model
  useEffect(() => {
    const handleResize = () => {
      if (sceneRef.current && sceneRef.current.renderer) {
        console.log('🔄 Window resized, updating 3D renderer...');
        const viewer = viewerRef.current;
        if (viewer) {
          const width = viewer.clientWidth;
          const height = viewer.clientHeight;
          sceneRef.current.renderer.setSize(width, height);
          sceneRef.current.camera.aspect = width / height;
          sceneRef.current.camera.updateProjectionMatrix();
        }
      }
    };

    window.addEventListener('resize', handleResize);
    
    return () => {
      window.removeEventListener('resize', handleResize);
      if (sceneRef.current) {
        if (sceneRef.current.renderer) {
          sceneRef.current.renderer.dispose();
        }
        if (sceneRef.current.controls) {
          sceneRef.current.controls.dispose();
        }
        sceneRef.current = null;
      }
    };
  }, []);

  // Theo dõi khi viewerRef được khởi tạo
  useEffect(() => {
    if (viewerRef.current && has3DModel && animal.models && animal.models.length > 0) {
      console.log('🎯 viewerRef is now available, auto-loading 3D model...');
      const model3D = animal.models[0];
      load3DModel(`http://localhost/3d_web/animal-showcase/${model3D.path}`);
    }
  }, [has3DModel, animal.models]);

  // Load 3D model cho card nhỏ
  const load3DModel = (modelPath) => {
    console.log('🔄 Starting to load 3D model:', modelPath);
    
    if (!viewerRef.current) {
      console.log('❌ viewerRef.current is null');
      return;
    }

    const viewer = viewerRef.current;
    console.log('✅ viewerRef found, dimensions:', viewer.clientWidth, 'x', viewer.clientHeight);
    
    let fullModelPath;
    
    // Nếu modelPath là URL đầy đủ, trích xuất đường dẫn file
    if (modelPath && modelPath.includes('serve_media.php?file=')) {
      const urlParams = new URLSearchParams(modelPath.split('?')[1]);
      const extractedPath = urlParams.get('file');
      fullModelPath = `http://localhost/3d_web/animal-showcase/${extractedPath}`;
      console.log('🔍 Extracted path from URL:', extractedPath);
    } else if (modelPath && modelPath.startsWith('uploads/')) {
      fullModelPath = `http://localhost/3d_web/animal-showcase/${modelPath}`;
    } else if (modelPath) {
      fullModelPath = modelPath;
    } else {
      console.log('❌ No model path provided');
      setStatusMessage('❌ Không có đường dẫn model');
      return;
    }

    console.log('🎯 Full model path:', fullModelPath);
    setIsLoading3D(true);
    setStatusMessage('⏳ Đang tải model 3D...');
    setModelError(false);

    // Cleanup scene cũ nếu có
    if (sceneRef.current) {
      console.log('🧹 Cleaning up old scene...');
      if (sceneRef.current.renderer) {
        sceneRef.current.renderer.dispose();
      }
      if (sceneRef.current.controls) {
        sceneRef.current.controls.dispose();
      }
      sceneRef.current = null;
    }

    try {
      console.log('🎨 Setting up Three.js scene...');
      const scene = new THREE.Scene();
      scene.background = new THREE.Color(0x1a1a1a); // Dark background
      
      const camera = new THREE.PerspectiveCamera(75, viewer.clientWidth / viewer.clientHeight, 0.1, 1000);
      camera.position.z = 5;
      
      const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
      renderer.setSize(viewer.clientWidth, viewer.clientHeight);
      renderer.setPixelRatio(window.devicePixelRatio);
      renderer.setClearColor(0x1a1a1a, 1);
      
      viewer.innerHTML = '';
      viewer.appendChild(renderer.domElement);
      
      console.log('🎮 Setting up OrbitControls...');
      const controls = new OrbitControls(camera, renderer.domElement);
      controls.enableDamping = true;
      controls.dampingFactor = 0.05;
      controls.autoRotate = true;
      controls.autoRotateSpeed = 2;
      
      console.log('💡 Adding lights...');
      const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
      scene.add(ambientLight);
      
      const directionalLight = new THREE.DirectionalLight(0xffffff, 0.9);
      directionalLight.position.set(10, 10, 5);
      scene.add(directionalLight);
      
      console.log('📦 Starting GLTFLoader...');
      const loader = new GLTFLoader();
      loader.load(
        fullModelPath,
        (gltf) => {
          console.log('✅ 3D model loaded successfully:', gltf);
          
          const model = gltf.scene;
          
          const box = new THREE.Box3().setFromObject(model);
          const size = box.getSize(new THREE.Vector3());
          const maxDim = Math.max(size.x, size.y, size.z);
          const scale = 3 / maxDim;
          model.scale.setScalar(scale);
          
          const center = box.getCenter(new THREE.Vector3());
          model.position.sub(center.multiplyScalar(scale));
          
          scene.add(model);
          
          console.log('🎬 Starting animation loop...');
          const animate = () => {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
          };
          animate();
          
          setIsLoading3D(false);
          setStatusMessage('✅ Model 3D đã tải thành công!');
          
          sceneRef.current = { scene, renderer, controls };
          console.log('🎉 3D scene setup complete!');
        },
        (progress) => {
          const percent = Math.round((progress.loaded / progress.total) * 100);
          console.log(`⏳ Loading progress: ${percent}%`);
          setStatusMessage(`⏳ Đang tải model 3D... ${percent}%`);
        },
        (error) => {
          console.error('❌ Error loading 3D model:', error);
          console.error('Error details:', error.message);
          setIsLoading3D(false);
          setModelError(true);
          setStatusMessage('❌ Lỗi tải model 3D: ' + error.message);
        }
      );
      
    } catch (error) {
      console.error('❌ Error setting up 3D scene:', error);
      setIsLoading3D(false);
      setModelError(true);
      setStatusMessage('❌ Lỗi khởi tạo 3D scene: ' + error.message);
    }
  };



  // Lấy text trạng thái
  const getStatusText = () => {
    if (has3DModel && !modelError) return '🎮 Có sẵn';
    if (has3DModel && modelError) return '⚠️ Lỗi tải model';
    return '⏳ Chờ admin upload';
  };

  return (
    <div className="animal-card bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-lg border border-white/20 rounded-2xl p-4 sm:p-6 hover:bg-white/25 transition-all duration-300 hover:scale-105 group shadow-lg hover:shadow-xl w-full">
      {/* Animal Image */}
              <div className="relative mb-4 group">
          <div className="relative overflow-hidden rounded-xl shadow-lg">
          <img
            src={getAnimalImage(animal)}
            alt={animal.name}
            className="w-full h-48 object-cover rounded-xl transition-all duration-300 group-hover:scale-105 group-hover:rotate-0"
            style={{
              mixBlendMode: 'multiply',
              filter: 'contrast(1.1) brightness(1.05) saturate(1.1)',
              backgroundColor: 'transparent',
              isolation: 'isolate'
            }}
            onError={(e) => {
              setImageError(true);
              e.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjMzM0MTU1Ii8+CjxwYXRoIGQ9Ik0xMDAgNjBDMTE2LjU2OSA2MCAxMzAgNzMuNDMxIDEzMCA5MEMxMzAgMTA2LjU2OSAxMTYuNTY5IDEyMCAxMDAgMTIwQzgzLjQzMSAxMjAgNzAgMTA2LjU2OSA3MCA5MEM3MCA3My40MzEgODMuNDMxIDYwIDEwMCA2MFoiIGZpbGw9IiM2QjcyOEEiLz4KPHBhdGggZD0iTTEwMCAxNDBDMTE2LjU2OSAxNDAgMTMwIDE1My40MzEgMTMwIDE3MEMxMzAgMTg2LjU2OSAxMTYuNTY5IDIwMCAxMDAgMjAwQzgzLjQzMSAyMDAgNzAgMTg2LjU2OSA3MCAxNzBDNzAgMTUzLjQzMSA4My40MzEgMTQwIDEwMCAxNDBaIiBmaWxsPSIjNkI3MjhBIi8+CjxwYXRoIGQ9Ik0xNDAgMTAwQzE0MCA4My40MzEgMTUzLjQzMSA3MCAxNzAgNzBDMTg2LjU2OSA3MCAyMDAgODMuNDMxIDIwMCAxMDBDMTk5LjkgMTE2LjU2OSAxODYuNTY5IDEzMCAxNzAgMTMwQzE1My40MzEgMTMwIDE0MCAxMTYuNTY5IDE0MCAxMDBaIiBmaWxsPSIjNkI3MjhBIi8+CjxwYXRoIGQ9Ik0zMCAxMDBDMzAgODMuNDMxIDQzLjQzMSA3MCA2MCA3MEM3Ni41NjkgNzAgOTAgODMuNDMxIDkwIDEwMEM5MCAxMTYuNTY5IDc2LjU2OSAxMzAgNjAgMTMwQzQzLjQzMSAxMzAgMzAgMTE2LjU2OSAzMCAxMDBaIiBmaWxsPSIjNkI3MjhBIi8+Cjwvc3ZnPgo=';
            }}
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 rounded-xl"></div>
        </div>
      </div>
        
      {/* 3D Model Viewer */}
      <div className="mt-4">
        <div className="text-white/80 text-sm font-medium mb-2">🎮 Model 3D</div>
        {has3DModel ? (
          <div 
            ref={viewerRef}
            className="w-full h-40 sm:h-48 md:h-56 lg:h-64 bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg overflow-hidden border border-white/20"
            style={{
              background: 'linear-gradient(135deg, #1f2937 0%, #111827 100%)',
              minHeight: '160px',
              maxHeight: '256px'
            }}
          />
        ) : (
          <div className="w-full h-40 sm:h-48 md:h-56 lg:h-64 bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-lg border border-white/20 flex items-center justify-center">
            <div className="text-center text-white/60">
              <div className="text-3xl sm:text-4xl mb-2">🎮</div>
              <p className="text-xs sm:text-sm">Chưa có model 3D</p>
            </div>
          </div>
        )}
        {isLoading3D && (
          <div className="text-center text-white/80 mt-2">
            <div className="animate-spin rounded-full h-4 w-4 sm:h-6 sm:w-6 border-b-2 border-blue-400 mx-auto"></div>
            <p className="mt-2 text-xs sm:text-sm">Đang tải model 3D...</p>
          </div>
        )}
      </div>

      {/* Animal Info */}
      <div className="text-white">
        <h3 className="text-lg sm:text-xl font-bold mb-2">{animal.name}</h3>
        <p className="text-white/80 mb-2 text-sm sm:text-base">Tên tiếng Anh: {animal.species_name || 'Chưa cập nhật'}</p>
        
        {/* Habitat Type */}
        <div className="flex items-center mb-2">
          <span className="text-white/60 text-sm mr-2">Khu vực sống:</span>
          <span className={`px-2 py-1 text-xs rounded-full ${
            animal.habitat === 'Dưới biển' ? 'bg-blue-100 text-blue-800' :
            animal.habitat === 'Trên trời' ? 'bg-purple-100 text-purple-800' :
            animal.habitat === 'Trên cạn' ? 'bg-green-100 text-green-800' :
            'bg-gray-100 text-gray-800'
          }`}>
            {animal.habitat || 'Chưa cập nhật'}
          </span>
        </div>
        
        {/* Conservation Status */}
        <div className="flex items-center mb-2">
          <span className="text-white/60 text-sm mr-2">Bảo tồn:</span>
          <span className={`px-2 py-1 text-xs rounded-full ${
            animal.conservation_status === 'Đã tuyệt chủng' ? 'bg-gray-200 text-gray-900' :
            animal.conservation_status === 'Cực kỳ nguy cấp' ? 'bg-red-200 text-red-900' :
            animal.conservation_status === 'Nguy cấp' ? 'bg-orange-100 text-orange-800' :
            animal.conservation_status === 'Dễ bị tổn thương' ? 'bg-yellow-100 text-yellow-800' :
            animal.conservation_status === 'Gần bị đe dọa' ? 'bg-yellow-100 text-yellow-800' :
            animal.conservation_status === 'Ít quan ngại' ? 'bg-blue-100 text-blue-800' :
            animal.conservation_status === 'Thiếu dữ liệu' ? 'bg-gray-100 text-gray-800' :
            animal.conservation_status === 'Chưa đánh giá' ? 'bg-gray-100 text-gray-800' :
            'bg-green-100 text-green-800'
          }`}>
            {animal.conservation_status || 'Chưa cập nhật'}
          </span>
        </div>
        
        {/* Population Count */}
        <div className="flex items-center mb-2">
          <span className="text-white/60 text-sm mr-2">Số lượng:</span>
          <span className="text-white/80 text-sm font-medium">
            {animal.population || 'Không xác định'}
          </span>
        </div>
        
        {animal.description && (
          <p className="text-white/60 mb-3">{animal.description}</p>
        )}
        
        {/* 3D Status */}
        <div className="flex items-center justify-between mb-4">
          <span className={`text-sm font-medium ${has3DModel && !modelError ? 'text-green-400' : has3DModel && modelError ? 'text-red-400' : 'text-yellow-400'}`}>
            🎮 {getStatusText()}
          </span>
          
          {!has3DModel && (
            <span className="text-sm text-white/60">
              ⏳ Chờ admin upload
            </span>
          )}
        </div>

        {/* Status Message */}
        {statusMessage && (
          <div className="text-sm text-white/80 bg-white/10 rounded-lg p-2 mb-3">
            {statusMessage}
          </div>
        )}

        {/* Action Buttons */}
        <div className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
          {has3DModel && !modelError && (
            <>
              <a
                href={downloadUrl}
                download
                className="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs sm:text-sm rounded-lg transition-colors duration-200 text-center"
              >
                📥 Tải xuống
              </a>
              <button
                onClick={() => {
                  if (animal.models && animal.models.length > 0) {
                    const model3D = animal.models[0];
                    // Debug: Log ra để kiểm tra
                    console.log('🔍 Model3D data:', model3D);
                    console.log('🔍 File path:', model3D?.path);
                    
                    // Sử dụng đường dẫn file trực tiếp
                    const modelUrl = model3D.path;
                    
                    console.log('🔍 Final Model URL:', modelUrl);
                    
                    const url = `http://localhost/3d_web/animal-showcase/3d-viewer.html?id=${animal.id}&name=${encodeURIComponent(animal.name)}&species=${encodeURIComponent(animal.species_name)}&model=${encodeURIComponent(modelUrl)}`;
                    console.log('🔍 Full 3D viewer URL:', url);
                    
                    window.open(url, '_blank');
                  } else {
                    console.error('❌ No 3D model available for this animal');
                    alert('Không có model 3D cho động vật này');
                  }
                }}
                className="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm rounded-lg transition-colors duration-200 text-center"
              >
                🎮 Xem Toàn Màn Hình
              </button>
            </>
          )}
          
          {has3DModel && modelError && (
            <button
              onClick={() => {
                if (animal.models && animal.models.length > 0) {
                  const model3D = animal.models[0];
                  load3DModel(`http://localhost/3d_web/animal-showcase/${model3D.path}`);
                }
              }}
              className="px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs sm:text-sm rounded-lg transition-colors duration-200 text-center"
            >
              🔄 Thử lại
            </button>
          )}
          
          {!has3DModel && (
            <button
              onClick={() => {
                if (animal.models && animal.models.length > 0) {
                  setHas3DModel(true);
                  const model3D = animal.models[0];
                  load3DModel(`http://localhost/3d_web/animal-showcase/${model3D.path}`);
                }
              }}
              className="px-3 py-2 bg-green-600 hover:bg-blue-700 text-white text-xs sm:text-sm rounded-lg transition-colors duration-200 text-center"
            >
              🔄 Kiểm tra
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default AnimalCard;
